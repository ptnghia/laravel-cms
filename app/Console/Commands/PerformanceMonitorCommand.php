<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'performance:monitor 
                            {--detailed : Show detailed performance metrics}
                            {--cache : Test cache performance}
                            {--database : Test database performance}
                            {--memory : Show memory usage}
                            {--all : Run all performance tests}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor application performance metrics';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Laravel CMS Performance Monitor');
        $this->newLine();

        $options = $this->options();
        
        if ($options['all'] || (!$options['cache'] && !$options['database'] && !$options['memory'])) {
            $this->runAllTests();
        } else {
            if ($options['cache']) {
                $this->testCachePerformance();
            }
            
            if ($options['database']) {
                $this->testDatabasePerformance();
            }
            
            if ($options['memory']) {
                $this->showMemoryUsage();
            }
        }

        $this->newLine();
        $this->info('✅ Performance monitoring completed');
        
        return Command::SUCCESS;
    }

    /**
     * Run all performance tests
     */
    protected function runAllTests(): void
    {
        $this->testCachePerformance();
        $this->newLine();
        $this->testDatabasePerformance();
        $this->newLine();
        $this->showMemoryUsage();
        $this->newLine();
        $this->showSystemInfo();
    }

    /**
     * Test cache performance
     */
    protected function testCachePerformance(): void
    {
        $this->info('📊 Cache Performance Test');
        
        // Test cache stats
        $stats = CacheService::getStats();
        $this->table(['Metric', 'Value'], [
            ['Driver', $stats['driver']],
            ['Status', $stats['status']],
            ['Test Result', $stats['test'] ?? 'N/A'],
        ]);

        // Test cache speed
        $this->info('Testing cache speed...');
        
        $iterations = 100;
        $startTime = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++) {
            Cache::put("test_key_{$i}", "test_value_{$i}", 60);
            Cache::get("test_key_{$i}");
            Cache::forget("test_key_{$i}");
        }
        
        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $avgTime = $totalTime / $iterations;
        
        $this->table(['Cache Operation', 'Time'], [
            ['Total time for ' . $iterations . ' operations', number_format($totalTime, 2) . ' ms'],
            ['Average time per operation', number_format($avgTime, 2) . ' ms'],
            ['Operations per second', number_format(1000 / $avgTime, 0)],
        ]);

        // Test cache warm-up
        $this->info('Testing cache warm-up...');
        $startTime = microtime(true);
        $warmed = CacheService::warmUp();
        $endTime = microtime(true);
        $warmUpTime = ($endTime - $startTime) * 1000;
        
        $this->line("Cache warm-up completed in " . number_format($warmUpTime, 2) . " ms");
        $this->line("Warmed caches: " . implode(', ', array_keys($warmed)));
    }

    /**
     * Test database performance
     */
    protected function testDatabasePerformance(): void
    {
        $this->info('🗄️  Database Performance Test');
        
        // Test basic queries
        $queries = [
            'Users count' => fn() => User::count(),
            'Posts count' => fn() => Post::count(),
            'Categories count' => fn() => Category::count(),
            'Published posts' => fn() => Post::where('status', 'published')->count(),
            'Active categories' => fn() => Category::where('is_active', true)->count(),
        ];

        $results = [];
        foreach ($queries as $name => $query) {
            $startTime = microtime(true);
            $result = $query();
            $endTime = microtime(true);
            $time = ($endTime - $startTime) * 1000;
            
            $results[] = [
                $name,
                $result,
                number_format($time, 2) . ' ms'
            ];
        }
        
        $this->table(['Query', 'Result', 'Time'], $results);

        // Test complex query performance
        $this->info('Testing complex queries...');
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        $posts = Post::with(['category', 'author', 'tags'])
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->limit(10)
            ->get();
            
        $endTime = microtime(true);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        
        $complexQueryTime = ($endTime - $startTime) * 1000;
        
        $this->table(['Complex Query Metric', 'Value'], [
            ['Posts retrieved', $posts->count()],
            ['Query time', number_format($complexQueryTime, 2) . ' ms'],
            ['Number of queries', count($queries)],
            ['Average query time', number_format($complexQueryTime / count($queries), 2) . ' ms'],
        ]);

        // Show slow queries if any
        $slowQueries = array_filter($queries, fn($query) => $query['time'] > 100);
        if (!empty($slowQueries)) {
            $this->warn('⚠️  Slow queries detected (>100ms):');
            foreach ($slowQueries as $query) {
                $this->line("- " . number_format($query['time'], 2) . "ms: " . substr($query['query'], 0, 100) . "...");
            }
        }
    }

    /**
     * Show memory usage
     */
    protected function showMemoryUsage(): void
    {
        $this->info('💾 Memory Usage');
        
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = ini_get('memory_limit');
        
        $this->table(['Memory Metric', 'Value'], [
            ['Current usage', $this->formatBytes($memoryUsage)],
            ['Peak usage', $this->formatBytes($memoryPeak)],
            ['Memory limit', $memoryLimit],
            ['Usage percentage', number_format(($memoryUsage / $this->parseBytes($memoryLimit)) * 100, 2) . '%'],
        ]);

        // Test memory efficiency
        $this->info('Testing memory efficiency...');
        
        $initialMemory = memory_get_usage(true);
        
        // Create large dataset
        $data = [];
        for ($i = 0; $i < 10000; $i++) {
            $data[] = [
                'id' => $i,
                'title' => 'Test Title ' . $i,
                'content' => str_repeat('Lorem ipsum dolor sit amet. ', 50),
            ];
        }
        
        $afterCreation = memory_get_usage(true);
        
        // Process data
        $processed = array_map(function ($item) {
            return [
                'id' => $item['id'],
                'title' => $item['title'],
                'excerpt' => substr($item['content'], 0, 100),
            ];
        }, $data);
        
        $afterProcessing = memory_get_usage(true);
        
        // Clean up
        unset($data, $processed);
        $afterCleanup = memory_get_usage(true);
        
        $this->table(['Memory Test', 'Usage'], [
            ['Initial', $this->formatBytes($initialMemory)],
            ['After creating 10k records', $this->formatBytes($afterCreation)],
            ['After processing', $this->formatBytes($afterProcessing)],
            ['After cleanup', $this->formatBytes($afterCleanup)],
            ['Memory increase', $this->formatBytes($afterCreation - $initialMemory)],
            ['Memory efficiency', number_format((($afterCleanup - $initialMemory) / ($afterCreation - $initialMemory)) * 100, 2) . '% retained'],
        ]);
    }

    /**
     * Show system information
     */
    protected function showSystemInfo(): void
    {
        $this->info('🖥️  System Information');
        
        $this->table(['System Metric', 'Value'], [
            ['PHP Version', PHP_VERSION],
            ['Laravel Version', app()->version()],
            ['Environment', app()->environment()],
            ['Debug Mode', config('app.debug') ? 'Enabled' : 'Disabled'],
            ['Cache Driver', config('cache.default')],
            ['Database Driver', config('database.default')],
            ['Queue Driver', config('queue.default')],
            ['Session Driver', config('session.driver')],
            ['Timezone', config('app.timezone')],
            ['Locale', config('app.locale')],
        ]);

        // Show database info
        try {
            $dbSize = DB::select("SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB' 
                FROM information_schema.tables 
                WHERE table_schema = ?", [config('database.connections.mysql.database')]);
            
            if (!empty($dbSize)) {
                $this->line("Database size: " . $dbSize[0]->{'DB Size in MB'} . " MB");
            }
        } catch (\Exception $e) {
            // Ignore database size check if not available
        }
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Parse memory limit string to bytes
     */
    protected function parseBytes(string $val): int
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int) $val;
        
        switch ($last) {
            case 'g':
                $val *= 1024;
                // no break
            case 'm':
                $val *= 1024;
                // no break
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }
}
