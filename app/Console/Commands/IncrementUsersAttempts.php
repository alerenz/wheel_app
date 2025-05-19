<?php

namespace App\Console\Commands;

use App\Services\ActiveWheelService;
use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncrementUsersAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:increment-users-attempts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Increment attempts for users based on active wheels';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::transaction(function () {
            try {
                $currentDayIndex = date('w');
                $max_attempts = config('custom.max_attempts');
                
                $daysMap = [
                    0 => 'воскресенье',
                    1 => 'понедельник',
                    2 => 'вторник',
                    3 => 'среда',
                    4 => 'четверг',
                    5 => 'пятница',
                    6 => 'суббота'
                ];
            
                $currentDay = $daysMap[$currentDayIndex];

                $wheel = ActiveWheelService::getActiveWheel();
                if($wheel != null){
                    $wheelDays = $wheel->days_of_week;
                    if (in_array($currentDay, $wheelDays)) {
                        User::where('attempts', '<', $max_attempts)
                                ->increment('attempts');
                        
                        Log::info(message: "Попытки для прокрутки колеса {$wheel->name} увеличились для всех пользователей");
                        $this->info("Попытки для прокрутки колеса {$wheel->name} увеличились для всех пользователей");
                    }
                }else{
                    Log::info("Нет активного колеса");
                    $this->info("Нет активного колеса");
                }
            }catch(\Exception $e){
                Log::error("Ошибка при инкременте попыток: " . $e->getMessage());
                throw $e;
            }
        });
        
    }
}
