<?php

namespace App\Jobs;

use App\Traits\SftpConnectionTrait;
use App\Models\Cart;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use App\Models\Conciliation;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use XMLReader;
use SimpleXMLElement;

class FtpConciliationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SftpConnectionTrait;
    private $fechaPagos;
    private $fileName;
    
    public function __construct()
    {
        //
    }

    public function handle(): void
    {
       
            $sftp = $this->testConnection();
            $fileList = $sftp->nlist('/');
           
            foreach ($fileList as $fileName) {
            
                if ($fileName === '.' || $fileName === '..') {
                    continue;
                }

                try {    
                    $fileContent = $sftp->get($fileName);
                    
                    $this->fileName=$fileName;

                    if($this->fileNameProcess($this->fileName)){
                        Log::error("Archivo ya fue procesado ".$this->fileName);
                        $response = response()->json([
                            'error' => 500,
                            'message' => 'Archivo ya fue procesado'
                        ], 500);
                        continue;
                    }
                    
                    $xml = simplexml_load_string($fileContent);
                    dd($xml);
                    foreach ($xml->detallePagos as $detallePago) {
                        /*Pago::create([
                            'idCarro' => (int) $detallePago->idCarro,
                            'idConvenio' => (int) $detallePago->idConvenio,
                            'descProducto' => (string) $detallePago->descProducto,
                            'montoProducto' => (float) $detallePago->montoProducto,
                            'fechahoraOperacion' => (string) $detallePago->fechahoraOperacion,
                        ]);*/
                    }
                 
                    /*$arrayData=$this->convertXmlToArray($fileContent);

                    if($arrayData['totalizadorPagos']['numeroPagos']=="0"){
                        Log::error("Archivo no contiene pagos ".$this->fileName);
                        $response = response()->json([
                            'error' => 500,
                            'message' => 'Archivo no contiene pagos'
                        ], 500);
                        continue;
                    }

                    $detallePagos=$arrayData['detallePagos'];
                
                    if($arrayData['totalizadorPagos']['numeroPagos']=="1"){
                        $fechaOperacion=$detallePagos["fechahoraOperacion"];
                    }else{
                        $fechaOperacion=$detallePagos[0]["fechahoraOperacion"];
                    }
                    
                    if (!empty($fechaOperacion)) {
                        $dateTime = DateTime::createFromFormat("d/m/Y H:i:s", $fechaOperacion);
                        $this->fechaPagos = $dateTime ? $dateTime->format("Y-m-d") : null;
                    }else{
                        Log::error("Error proceso SFTP Santander: No hay fecha de pago ".$this->fileName);
                        throw new \Exception('Error proceso SFTP Santander');
                    }
                    $registrosCart = Cart::whereDate('car_created_at', '=',$this->fechaPagos)->get();
                    
                    $this->processData($registrosCart,$detallePagos);
                    dd($this->resume());*/

                } catch (\Exception $e) {
                    Log::error("Error proceso conciliacion Santander" . $e->getMessage());
                    $response = response()->json([
                        'error' => 500,
                        'message' => 'Error SFTP'
                    ], 500);
                    continue;
                }
                
            }
    }

    public function processData($array_cart, $array_santander){
       
        $cart = $array_cart->map(function ($registro) {
            return [
                'idCarro' => $registro->car_id,
                'montoProducto' => $registro->car_flow_amount,
            ];
        })->all();  
        
        if (is_array($array_santander)) {
            $santander=$array_santander;
            $idCarroBuscar = $santander['idCarro'];
                $status = 'NO EXISTE';
    
                foreach ($cart as $registro) {
                    if ($registro['idCarro'] == $idCarroBuscar) {
                        if ($santander['montoProducto'] == $registro['montoProducto']) {
                            $status = 'OK';
                        } else {
                            $status = 'INCONSISTENCIA PAGO';
                        }
                        break; 
                    }
                }

                $santander['status']=$status;
        }else{
            $santander = array_map(function ($detalle) {
                return [
                    'idCarro' => $detalle['idCarro'],
                    'idConvenio' => $detalle['idConvenio'],
                    'numeroProducto' => $detalle['numeroProducto'],
                    'numeroCliente' => $detalle['numeroCliente'],
                    'expiracionProducto' => $detalle['expiracionProducto'],
                    'descProducto' => $detalle['descProducto'],
                    'montoProducto' => $detalle['montoProducto'],
                    'idAtributo' => $detalle['idAtributo'],
                    'numeroOperacion' => $detalle['numeroOperacion'],
                    'fechahoraOperacion' => $detalle['fechahoraOperacion']
                ];
                
            }, $array_santander);

            foreach ($santander as &$pago) {

                $idCarroBuscar = $pago['idCarro'];
                $status = 'NO EXISTE';
    
                foreach ($cart as $registro) {
                    if ($registro['idCarro'] == $idCarroBuscar) {
                        if ($pago['montoProducto'] == $registro['montoProducto']) {
                            $status = 'OK';
                        } else {
                            $status = 'INCONSISTENCIA PAGO';
                        }
                        break; 
                    }
                }
    
                $pago['status']=$status;
            }
    
        }
        
        $this->insertData($santander);
        $resumen = $this->resume();
        return response()->json($resumen);

    }
    private function insertData(Array $array){
       
        try {
            DB::beginTransaction();
            if(is_array($array)){
                $numeroProducto = is_array($array['numeroProducto']) ? '' : $array['numeroProducto'];
                $numeroCliente = is_array($array['numeroCliente']) ? '' : $array['numeroCliente'];
                $numeroOperacion = is_array($array['numeroOperacion']) ? 0 : $array['numeroOperacion'];
                $fechahoraOperacion = Carbon::createFromFormat('d/m/Y H:i:s', $array['fechahoraOperacion'])->format('Y-m-d H:i:s');
                Conciliation::create([
                    'con_cart_id' => $array['idCarro'],
                    'con_agreement_id' => $array['idConvenio'],
                    'con_product_number' => $numeroProducto,
                    'con_customer_number' => $numeroCliente,
                    'con_product_expiration' => $fechahoraOperacion,
                    'con_product_description' => $array['descProducto'],
                    'con_product_amount' => $array['montoProducto'],
                   // 'idAtributo' => $array['idAtributo'],
                    'con_operation_number' => $numeroOperacion,
                    'con_operation_date' => $fechahoraOperacion,
                    'con_status' => $array['status'],
                    'con_file_process'  => $this->fileName
                ]);
            }else{
                foreach ($array as $data) {

                    $numeroProducto = is_array($data['numeroProducto']) ? '' : $data['numeroProducto'];
                    $numeroCliente = is_array($data['numeroCliente']) ? '' : $data['numeroCliente'];
                    $numeroOperacion = is_array($data['numeroOperacion']) ? 0 : $data['numeroOperacion'];
                    $fechahoraOperacion = Carbon::createFromFormat('d/m/Y H:i:s', $data['fechahoraOperacion'])->format('Y-m-d H:i:s');
                    Conciliation::create([
                        'con_cart_id' => $data['idCarro'],
                        'con_agreement_id' => $data['idConvenio'],
                        'con_product_number' => $numeroProducto,
                        'con_customer_number' => $numeroCliente,
                        'con_product_expiration' => $fechahoraOperacion,
                        'con_product_description' => $data['descProducto'],
                        'con_product_amount' => $data['montoProducto'],
                    // 'idAtributo' => $data['idAtributo'],
                        'con_operation_number' => $numeroOperacion,
                        'con_operation_date' => $fechahoraOperacion,
                        'con_status' => $data['status'],
                        'con_file_process'  => $this->fileName
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function resume() {
        try {
            $totalRegistros = Conciliation::whereRaw('DATE_FORMAT(con_operation_date, "%Y-%m-%d") = ?', [$this->fechaPagos])
            ->count();
         
            $resumenStatus = Conciliation::select('con_status', DB::raw('count(*) as cantidad'))
            ->whereRaw('DATE_FORMAT(con_operation_date, "%Y-%m-%d") = ?', [$this->fechaPagos])
            ->groupBy('con_status')
            ->get()
            ->pluck('cantidad', 'con_status')
            ->toArray();
    
            $resumen = [
                'totalRegistros' => $totalRegistros,
                'resumenStatus' => $resumenStatus,
            ];
    
            return $resumen;
        } catch (\Exception $e) {
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
