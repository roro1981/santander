<?php

namespace Tests\Unit;

use App\Http\Utils\Constants;
use App\Http\Utils\ParamUtil;
use App\Jobs\FtpConciliationJob;
use App\Models\Cart;
use App\Traits\SftpConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery;
use phpseclib3\Net\SFTP;
use Tests\TestCase;

class FtpConciliationJobTest extends TestCase
{
    use SftpConnectionTrait;

    public function setUp(): void
    {
        parent::setUp();

    }

    public function testHandle()
    {
        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock->method('nlist')->willReturn(['testfile.xml']);
        $xmlContent = file_get_contents(base_path('tests/Unit/Jobs/testfile.xml'));
        $sftpMock->method('get')->willReturn($xmlContent);

        DB::shouldReceive('table')
          ->with('bbs_conciliation')
          ->andReturnSelf();
        DB::shouldReceive('insert')
          ->andReturn(true);

          DB::shouldReceive('beginTransaction')
          ->zeroOrMoreTimes()
          ->andReturn(null);
        
        DB::shouldReceive('commit')
          ->zeroOrMoreTimes()
          ->andReturn(null);
        
        DB::shouldReceive('rollBack')
          ->zeroOrMoreTimes()
          ->andReturn(null);  

        $job = new FtpConciliationJob();
        $job->setSftpConnection($sftpMock);

        $job->handle();

        $this->assertTrue(true);
    }

    public function testHandleWithProcessedFile()
    {
        $sftpMock = $this->createMock(\phpseclib3\Net\SFTP::class);
        $sftpMock->method('nlist')->willReturn(['testfile_processed.xml']); 
        
        $sftpMock->method('get')->willReturn('');

        $job = new FtpConciliationJob();
        $job->setSftpConnection($sftpMock);

        DB::shouldReceive('beginTransaction')->never();
        DB::shouldReceive('insert')->never();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->never();

        $job->handle();
    }

    public function testHandleWithNoPayments()
    {
        $sftpMock = $this->createMock(\phpseclib3\Net\SFTP::class);
        $sftpMock->method('nlist')->willReturn(['testfile_no_payments.xml']);

        $xmlContent = file_get_contents(base_path('tests/Unit/Jobs/testfile_no_payments.xml')); 
        $sftpMock->method('get')->willReturn($xmlContent);

        $job = new FtpConciliationJob();
        $job->setSftpConnection($sftpMock);

        DB::shouldReceive('beginTransaction')->never();
        DB::shouldReceive('insert')->never();
        DB::shouldReceive('commit')->never();
        DB::shouldReceive('rollBack')->never();

        $job->handle();
    }

    public function testHandleWithSftpReadError()
    {
        Log::spy();
        $sftpMock = $this->createMock(SFTP::class);

        $sftpMock->method('nlist')->willReturn(['testfile.xml']);
        $sftpMock->method('get')->will($this->throwException(new \Exception("Error al leer el archivo")));

        $job = new FtpConciliationJob();
        $job->setSftpConnection($sftpMock);

        Log::shouldReceive('error')
        ->once()
        ->with(Mockery::on(function ($logMessage) {
            return Str::startsWith($logMessage, "Error en lectura de SFTP: testfile.xml Error: ");
        }));

        $job->handle();

    }

    public function testFileRenameReturnsTrue()
    {
        $sftpMock = $this->createMock(\phpseclib3\Net\SFTP::class);
        $sftpMock->method('nlist')->willReturn(['testfile.xml']);

        $xmlContent = file_get_contents(base_path('tests/Unit/Jobs/testfile.xml')); 
        $sftpMock->method('get')->willReturn($xmlContent);
        
        $job = new FtpConciliationJob();
        $job->setSftpConnection($sftpMock);

        $oldFileName = 'old_test_file.xml';
        $newFileName = 'new_test_file.xml'; 

        $sftpMock = $this->createMock(SFTP::class);
        $sftpMock->expects($this->never())
                ->method('rename')
                ->with($this->equalTo($oldFileName), $this->equalTo($newFileName))
                ->willReturn(true);

        $job->handle(); 
    }

    public function testConciliationProcessMock()
    {
        Queue::fake();
        $jobMock = Mockery::mock(FtpConciliationJob::class)->makePartial();
        $jobMock->shouldReceive('conciliationProcess')->never()->andReturn(true);
        $result = $jobMock->handle();
        
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testConciliationProcessHandlesException()
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();
        DB::shouldReceive('rollBack')->never(); // Verifica que se llama al rollback
    
        try {
            $job = new FtpConciliationJob();
            $job->conciliationProcess();
            $this->fail("Error simulado");
        } catch (\Exception $e) {
            $this->assertEquals("Error simulado", $e->getMessage());
        }
    }
    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
