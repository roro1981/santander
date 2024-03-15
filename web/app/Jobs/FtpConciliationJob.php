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
                    $fileContent = $sftp->get($fileName);
                    
                    $this->fileName=$fileName;

                    if($this->fileNameProcess($this->fileName)){
                        Log::error("Archivo ya fue procesado ".$this->fileName);
                        continue;
                    }
                    $xml = simplexml_load_string($fileContent);

                    $totalizador=$xml->totalizadorPagos;

                    if($totalizador->numeroPagos==0 && $totalizador->montoTotal==0){
                        Log::error("Archivo sin pagos ".$this->fileName);
                        continue;
                    }
                    
                    foreach ($xml->detallePagos as $detallePago) {
                        $idCart=Cart::where('car_id', '=',$detallePago->idCarro)->first();
                        $idConciliation=Conciliation::where('con_cart_id', '=',$detallePago->idCarro)
                        ->where('con_status', '=','OK')->where('con_transaction_process', '=',1)->first();
                        if($idConciliation){
                            continue;
                        }else{              
                            if($idCart){    
                                $detallePago->status="OK";
                                $detallePago->trx_process=1;
                                if($idCart->car_flow_amount != $detallePago->montoProducto){
                                    $detallePago->status="INCONSISTENCIA PAGO";
                                    $detallePago->trx_process=0;
                                }    
                            }else{
                                $detallePago->status="NO EXISTE";
                                $detallePago->trx_process=0;
                            }
                            $fechahoraOperacion = Carbon::createFromFormat('d/m/Y H:i:s', (string) $detallePago->fechahoraOperacion)->format('Y-m-d H:i:s');
                            $dataToInsert[] = [
                                'con_cart_id' => (string) $detallePago->idCarro,
                                'con_agreement_id' => (string) $detallePago->idConvenio,
                                'con_product_number' => isset($detallePago->numeroProducto) ? (string) $detallePago->numeroProducto : '-',
                                'con_customer_number' => isset($detallePago->numeroCliente) ? (string) $detallePago->numeroCliente : '-',
                                'con_product_expiration' => !empty($detallePago->expiracionProducto) ? (string) $detallePago->expiracionProducto : null,
                                'con_product_description' => (string) $detallePago->descProducto,
                                'con_product_amount' => (string) $detallePago->montoProducto,
                                'con_operation_number' => isset($detallePago->numeroOperacion) ? (int) $detallePago->numeroOperacion : 0,
                                'con_operation_date' => (string) $fechahoraOperacion,
                                'con_status' => (string) $detallePago->status,
                                'con_file_process'=> $this->fileName,
                                'con_transaction_process' => (string) $detallePago->trx_process,
                            ];
                        }    
                    }
                    if(count($dataToInsert)>0){
                        Log::info("Archivo ".$this->fileName." procesado correctamente");
                    }
                } catch (\Exception $e) {
                    Log::error("Error proceso conciliacion Santander en archivo: ".$this->fileName." Error: ".$e->getMessage());
                    exit;
                }
                
            }

            if(count($dataToInsert)>0){
                $this->insertData($dataToInsert);
            }
            Log::info("Conciliacion Santander finalizada");
    }

    private function insertData($array){
       
        try {
            DB::beginTransaction();
            Conciliation::insert($array);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error conciliacion Santander al grabar ".$e->getMessage());
            throw $e;
        }
    }

    public function fileNameProcess($file){
        $existeArchivo = Conciliation::where('con_file_process', $file)->exists();
    
        if ($existeArchivo) {
           return true;
        } else {
            return false;
        }
    }
}
