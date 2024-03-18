<?php

namespace App\Jobs;

use App\Traits\SftpConnectionTrait;
use App\Models\Cart;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Conciliation;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class FtpConciliationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SftpConnectionTrait;
    private $fechaPagos;
    private $fileName;

    public function handle(): void
    {
       
            $sftp = $this->testConnection();
            $fileList = $sftp->nlist('/');
            $dataToInsert = [];
            
            foreach ($fileList as $fileName) {
                
                if ($fileName === '.' || $fileName === '..') {
                    continue;
                }

                try {    
                    
                    $this->fileName=$fileName;

                    if($this->fileNameProcess($this->fileName)){
                        Log::error("Archivo ya fue procesado ".$this->fileName);
                        continue;
                    }
                    $fileContent = $sftp->get($fileName);
                    $xml = simplexml_load_string($fileContent);
                    $totalizador=$xml->totalizadorPagos;

                    if($totalizador->numeroPagos==0 && $totalizador->montoTotal==0){
                        Log::error("Archivo sin pagos ".$this->fileName);
                        continue;
                    }
                    $xml = simplexml_load_string($fileContent);
                    foreach ($xml->detallePagos as $detallePago) {
                        $fechahoraOperacion = Carbon::createFromFormat('d/m/Y H:i:s', (string) $detallePago->fechahoraOperacion)->format('Y-m-d H:i:s');
                        $dataToInsert[]= [
                            'con_cart_id' => (string) $detallePago->idCarro,
                            'con_agreement_id' => (string) $detallePago->idConvenio,
                            'con_product_number' => isset($detallePago->numeroProducto) ? (string) $detallePago->numeroProducto : '-',
                            'con_customer_number' => isset($detallePago->numeroCliente) ? (string) $detallePago->numeroCliente : '-',
                            'con_product_expiration' => !empty($detallePago->expiracionProducto) ? (string) $detallePago->expiracionProducto : null,
                            'con_product_description' => (string) $detallePago->descProducto,
                            'con_product_amount' => (string) $detallePago->montoProducto,
                            'con_operation_number' => isset($detallePago->numeroOperacion) ? (int) $detallePago->numeroOperacion : 0,
                            'con_operation_date' => (string) $fechahoraOperacion
                        ];
                    }   
      
                    if(!$this->fileRename($this->fileName,$sftp)){
                        Log::error("Problema al renombrar archivo: ".$this->fileName);
                    }else{
                        Log::info("Archivo procesado: ".$this->fileName);
                    }
                    
                } catch (\Exception $e) {
                    Log::error("Error en lectura de SFTP: ".$this->fileName." Error: ".$e->getMessage());
                }
                
            }

            if(count($dataToInsert)>0){
                $this->insertData($dataToInsert);
                Log::info("Transacciones SFTP grabadas en base de datos");
                $this->conciliationProcess2();
            }
            
    }

    private function insertData($array){
        try {
            DB::beginTransaction();
            Conciliation::insert($array);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al grabar datos desde SFTP: ".$e->getMessage());
            throw $e;
        }
    }

    public function fileNameProcess($file){
        if (Str::contains(Str::lower($file), '_process')) {
            return true;
        } else {
            return false;
        }
        
    }

    public function fileRename($file,$sftp){

        $ext_archivo = pathinfo($file, PATHINFO_EXTENSION);
        $nom_archivo = pathinfo($file, PATHINFO_FILENAME);

        $archivoOriginal = $file;
        $nuevoNombreArchivo = $nom_archivo.'_'.Carbon::now('America/Santiago')->format('YmdHisv')."_process.".$ext_archivo;
        
        if ($sftp->rename($archivoOriginal, $nuevoNombreArchivo)) {
            return true;
        } else {
            return false;
        }
        
    }
   
    public function conciliationProcess(){

        $idsParaMantener = Conciliation::selectRaw('MAX(con_id) as id')
                            ->groupBy('con_cart_id')
                            ->pluck('id');
        Conciliation::whereNotIn('con_id', $idsParaMantener)->delete();

        Conciliation::where('con_transaction_process', 0)->lazy()->each(function ($transaction) {
            DB::beginTransaction();

            try {
                $matchedTransaction = Cart::where('car_id', $transaction->con_cart_id)->first();
                if (!$matchedTransaction) {
                    $transaction->update([
                        'con_status' => 'NO_EXISTE',
                    ]);
                } else if ($matchedTransaction->car_status !== 'AUTHORIZED') {
                    $transaction->update([
                        'con_status' => 'TRANSACCION_NO_AUTORIZADA',
                    ]);
                } else {
                    $isAmountMatch = $transaction->con_product_amount == $matchedTransaction->car_flow_amount;
                    $isDateMatch = Carbon::parse($transaction->con_operation_date)->equalTo(Carbon::parse($matchedTransaction->car_transaction_date));

                    if (!$isAmountMatch) {
                        $transaction->update([
                            'con_status' => 'MONTO_INCONSISTENTE',
                        ]);
                    } elseif (!$isDateMatch) {
                        $transaction->update([
                            'con_status' => 'FECHA_TRX_INCONSISTENTE',
                        ]);
                    } else {
                        $transaction->update([
                            'con_status' => 'OK',
                            'con_transaction_process' => 1
                        ]);
                    }
                }

                DB::commit();
                Log::info("Proceso de conciliación finalizado");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error en el proceso de conciliacion: ".$e->getMessage());
                throw $e;
            }
        });
    }

    public function conciliationProcess2(){

        $idsParaMantener = Conciliation::selectRaw('MAX(con_id) as id')
                            ->groupBy('con_cart_id')
                            ->pluck('id');
        Conciliation::whereNotIn('con_id', $idsParaMantener)->delete();

        Conciliation::where('con_transaction_process', 0)
        ->orderBy('con_id')
        ->chunk(1000, function ($transactions) {
            foreach ($transactions as $transaction) {
                DB::beginTransaction();

                try {
                    $matchedTransaction = Cart::where('car_id', $transaction->con_cart_id)->first();

                    if (!$matchedTransaction) {
                        $transaction->update([
                            'con_status' => 'NO_EXISTE',
                        ]);
                    } else if ($matchedTransaction->car_status !== 'AUTHORIZED') {
                        $transaction->update([
                            'con_status' => 'TRANSACCION_NO_AUTORIZADA',
                        ]);
                    } else {
                        $isAmountMatch = $transaction->con_product_amount == $matchedTransaction->car_flow_amount;
                        $isDateMatch = Carbon::parse($transaction->con_operation_date)->equalTo(Carbon::parse($matchedTransaction->car_transaction_date));

                        if (!$isAmountMatch) {
                            $transaction->update([
                                'con_status' => 'MONTO_INCONSISTENTE',
                            ]);
                        } elseif (!$isDateMatch) {
                            $transaction->update([
                                'con_status' => 'FECHA_TRX_INCONSISTENTE',
                            ]);
                        } else {
                            $transaction->update([
                                'con_status' => 'OK',
                                'con_transaction_process' => 1
                            ]);
                        }
                    }

                    DB::commit();
                    Log::info("Proceso de conciliación finalizado");
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                    Log::error("Error en el proceso de conciliacion: ".$e->getMessage());
                }
            }
        });
    }
    
}
