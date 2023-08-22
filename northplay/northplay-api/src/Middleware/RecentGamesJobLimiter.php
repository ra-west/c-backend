<?php
 
 namespace Northplay\NorthplayApi\Middleware;
 
use Closure;
use Illuminate\Support\Facades\Redis;
 
class RecentGamesJobLimiter
{
    /**
     * Process the queued job.
     *
     * @param  \Closure(object): void  $next
     */
    public function handle(object $job, Closure $next): void
    {
        Redis::throttle('key')->block(0)->allow(15)->every(3)
                ->then(function () use ($job, $next) {
                    // Lock obtained...
 
                    $next($job);
                }, function () use ($job) {
                    // Could not obtain lock...
 
                    $job->release(3);
                });
    }
}