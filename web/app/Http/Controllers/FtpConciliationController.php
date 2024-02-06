<?php

namespace App\Http\Controllers;

use App\Traits\SftpConnectionTrait;
use App\Models\Cart;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use App\Models\Conciliation; 

use Illuminate\Support\Facades\Log;

class FtpConciliationController extends Controller
{
    use SftpConnectionTrait;
    private $fechaPagos;
    private $fileName;

    public function conciliation()
    {
    
        try {
            $sftp = $this->testConnection();
            if ($sftp->nlist('/')) {
                $fileList = $sftp->nlist('/');
                $fileContent = $sftp->get($fileList[2]);
                $this->fileName=$fileList[2];

                if($this->fileNameProcess($this->fileName)){
                    Log::error("Archivo ya fue procesado");
                    $response = response()->json([
                        'error' => 500,
                        'message' => 'Archivo ya fue procesado'
                    ], 500);
                    return $response;
                }

                $arrayData=$this->convertXmlToArray($fileContent);
                $detallePagos=$arrayData['detallePagos'];
                
                if (!empty($detallePagos[0]["fechahoraOperacion"])) {
                    $dateTime = DateTime::createFromFormat("d/m/Y H:i:s", $detallePagos[0]["fechahoraOperacion"]);
                    $this->fechaPagos = $dateTime ? $dateTime->format("Y-m-d") : null;
                }else{
                    Log::error("Error proceso SFTP Santander: No hay fecha de pago");
                    throw new \Exception('Error proceso SFTP Santander');
                }
                $registrosCart = Cart::whereDate('car_created_at', '=',$this->fechaPagos)->get();

                $response=$this->processData($registrosCart,$detallePagos);
                
            }

        } catch (\Exception $e) {
            Log::error("Error proceso SFTP Santander" . $e->getMessage());
            $response = response()->json([
                'error' => 500,
                'message' => 'Error SFTP'
            ], 500);
            
        }

        return $response;
    }

    public function processData($array_cart, $array_santander){
       
        $cart = $array_cart->map(function ($registro) {
            return [
                'idCarro' => $registro->car_id,
                'montoProducto' => $registro->car_flow_amount,
            ];
        })->all();  

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

        $this->insertData($santander);
        $resumen = $this->resume();
        return response()->json($resumen);

    }
    private function insertData($array){

        try {
            DB::beginTransaction();
        
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
