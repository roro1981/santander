<?php
namespace Tests\Feature;

use App\Jobs\FtpConciliationJob;
use App\Models\Cart;
use App\Models\Conciliation;
use App\Services\SftpService;
use App\Traits\SftpConnectionTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class FtpConciliationJobTest extends TestCase
{
    use RefreshDatabase;
    use SftpConnectionTrait;

    public function testJobProcessesFilesAndSavesDataCorrectly()
    {
        /*Conciliation::create([
            'con_cart_id' => 167921,
            'con_agreement_id' => 9570,
            'con_product_number' => '',
            'con_customer_number' => '',
            'con_product_expiration' => null,
            'con_product_description' => 'Producto éxito',
            'con_product_amount' => 1001,
            'con_operation_number' => 0,
            'con_operation_date' => '2024-01-26 09:41:18'
        ]);*/
        Queue::fake();

        $job = new FtpConciliationJob(); // Ajusta esto si tu job requiere parámetros
        $job->handle();

        $this->assertDatabaseHas('bbs_conciliation', [
            'con_cart_id' => 167921,
            'con_agreement_id' => 9570,
            'con_product_number' => '',
            'con_customer_number' => '',
            'con_product_expiration' => null,
            'con_product_description' => 'Producto éxito',
            'con_product_amount' => 1001,
            'con_operation_number' => 0,
            'con_operation_date' => '2024-01-26 09:41:18'
        ]);

    }
}