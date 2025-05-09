<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecalculateUserPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:recalculate-points {--user-id= : ID của người dùng cụ thể}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tính toán lại tổng điểm cho người dùng dựa trên lịch sử tích điểm';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        $this->info('Bắt đầu tính toán lại điểm người dùng...');

        try {
            if ($userId) {
                // Tính toán lại điểm cho một người dùng cụ thể
                $user = User::find($userId);
                
                if (!$user) {
                    $this->error("Không tìm thấy người dùng với ID: {$userId}");
                    return 1;
                }
                
                $oldPoints = $user->totalpoint;
                $newPoints = $user->recalculateTotalPoints();
                
                $this->info("Đã tính toán lại điểm cho người dùng: {$user->full_name}");
                $this->info("- Điểm cũ: {$oldPoints}");
                $this->info("- Điểm mới: {$newPoints}");
                $this->info("- Chênh lệch: " . ($newPoints - $oldPoints));
            } else {
                // Tính toán lại điểm cho tất cả người dùng
                $bar = $this->output->createProgressBar(User::count());
                $bar->start();
                
                $totalUpdated = 0;
                $totalDifference = 0;
                
                DB::beginTransaction();
                
                // Lấy tất cả người dùng có trạng thái active
                $users = User::where('status', 'active')->get();
                
                foreach ($users as $user) {
                    $oldPoints = $user->totalpoint;
                    $newPoints = $user->recalculateTotalPoints();
                    
                    if ($oldPoints != $newPoints) {
                        $totalUpdated++;
                        $totalDifference += ($newPoints - $oldPoints);
                    }
                    
                    $bar->advance();
                }
                
                DB::commit();
                
                $bar->finish();
                $this->newLine(2);
                
                $this->info("Hoàn thành tính toán lại điểm cho tất cả người dùng.");
                $this->info("- Tổng số người dùng đã cập nhật: {$totalUpdated}");
                $this->info("- Tổng chênh lệch điểm: {$totalDifference}");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Đã xảy ra lỗi: " . $e->getMessage());
            
            if (!$userId) {
                DB::rollBack();
            }
            
            return 1;
        }
    }
} 