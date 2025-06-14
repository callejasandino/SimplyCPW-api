<?php

namespace App\Http\Controllers;

use App\Models\ClientJob;
use App\Models\Quote;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get completed jobs count
        $completedJobsCount = ClientJob::where('status', 'Completed')->count();

        // Get pending jobs count
        $pendingJobsCount = ClientJob::where('status', 'Pending')->count();

        // Get recent jobs (last hour only)
        $recentJobs = ClientJob::where('created_at', '>=', Carbon::now()->subHour())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent quotes (last hour only)
        $recentQuotes = Quote::where('created_at', '>=', Carbon::now()->subHour())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate total income from completed jobs
        $totalIncome = ClientJob::where('status', 'Completed')
            ->sum('price');

        // Calculate income from last 7 days vs previous 7 days for percentage change
        $currentWeekIncome = ClientJob::where('status', 'Completed')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->sum('price');

        $previousWeekIncome = ClientJob::where('status', 'Completed')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->sum('price');

        $incomeChangePercentage = 0;
        if ($previousWeekIncome > 0) {
            $incomeChangePercentage = round((($currentWeekIncome - $previousWeekIncome) / $previousWeekIncome) * 100, 1);
        } elseif ($currentWeekIncome > 0) {
            $incomeChangePercentage = 100; // New income this week
        }

        // Calculate job completion percentage changes
        $currentWeekCompletedJobs = ClientJob::where('status', 'Completed')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $previousWeekCompletedJobs = ClientJob::where('status', 'Completed')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->count();

        $completedJobsChangePercentage = 0;
        if ($previousWeekCompletedJobs > 0) {
            $completedJobsChangePercentage = round((($currentWeekCompletedJobs - $previousWeekCompletedJobs) / $previousWeekCompletedJobs) * 100, 1);
        } elseif ($currentWeekCompletedJobs > 0) {
            $completedJobsChangePercentage = 100;
        }

        // Calculate pending jobs percentage change
        $currentWeekPendingJobs = ClientJob::where('status', 'Pending')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $previousWeekPendingJobs = ClientJob::where('status', 'Pending')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->count();

        $pendingJobsChangePercentage = 0;
        if ($previousWeekPendingJobs > 0) {
            $pendingJobsChangePercentage = round((($currentWeekPendingJobs - $previousWeekPendingJobs) / $previousWeekPendingJobs) * 100, 1);
        } elseif ($currentWeekPendingJobs > 0) {
            $pendingJobsChangePercentage = 100;
        }

        // Calculate total jobs percentage change
        $currentWeekTotalJobs = ClientJob::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $previousWeekTotalJobs = ClientJob::where('created_at', '>=', Carbon::now()->subDays(14))
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->count();

        $totalJobsChangePercentage = 0;
        if ($previousWeekTotalJobs > 0) {
            $totalJobsChangePercentage = round((($currentWeekTotalJobs - $previousWeekTotalJobs) / $previousWeekTotalJobs) * 100, 1);
        } elseif ($currentWeekTotalJobs > 0) {
            $totalJobsChangePercentage = 100;
        }

        // Get all services for reference
        $allServices = Service::all()->keyBy('id');

        // Get services distribution per client job - improved logic
        $allJobs = ClientJob::whereNotNull('services')
            ->where('services', '!=', '')
            ->get();

        $servicesDistribution = [];
        $debugInfo = []; // For debugging purposes

        foreach ($allJobs as $job) {
            $services = $job->services;

            // Debug: collect info about services format
            $debugInfo[] = [
                'job_id' => $job->id,
                'services_raw' => $services,
                'services_type' => gettype($services),
                'services_is_array' => is_array($services),
                'services_count' => is_array($services) ? count($services) : 0,
            ];

            // Handle different data formats
            if (is_string($services)) {
                // Try to decode JSON if it's a string
                $decoded = json_decode($services, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $services = $decoded;
                }
            }

            if (is_array($services) && ! empty($services)) {
                foreach ($services as $serviceId) {
                    // Ensure serviceId is numeric
                    $serviceId = (int) $serviceId;

                    if ($serviceId > 0 && isset($allServices[$serviceId])) {
                        $serviceName = $allServices[$serviceId]->name;
                        if (! isset($servicesDistribution[$serviceName])) {
                            $servicesDistribution[$serviceName] = 0;
                        }
                        $servicesDistribution[$serviceName]++;
                    }
                }
            }
        }

        // If no services distribution found, create sample data for testing
        if (empty($servicesDistribution)) {
            $servicesDistribution = [
                'No Service Data' => 1,
            ];
        }

        // Get new clients count per day (last 7 days)
        $newClientsPerDay = ClientJob::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get();

        // Get recent jobs (general - not just last hour)
        $allRecentJobs = ClientJob::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $dashboardData = [
            'recent_jobs' => $allRecentJobs,
            'completed_jobs_count' => $completedJobsCount,
            'pending_jobs_count' => $pendingJobsCount,
            'recent_jobs_last_hour' => $recentJobs,
            'recent_quotes_last_hour' => $recentQuotes,
            'total_income' => $totalIncome,
            'services_distribution' => $servicesDistribution,
            'new_clients_per_day' => $newClientsPerDay,
            'summary' => [
                'total_jobs' => ClientJob::count(),
                'total_quotes' => Quote::count(),
                'total_services' => Service::count(),
                'completion_rate' => ClientJob::count() > 0 ?
                    round(($completedJobsCount / ClientJob::count()) * 100, 2) : 0,
            ],
            'debug_services' => $debugInfo, // Temporary debug info
            'income_change_percentage' => $incomeChangePercentage,
            'completed_jobs_change_percentage' => $completedJobsChangePercentage,
            'pending_jobs_change_percentage' => $pendingJobsChangePercentage,
            'total_jobs_change_percentage' => $totalJobsChangePercentage,
        ];

        return response()->json(
            [
                'status' => 'success',
                'data' => $dashboardData,
            ],
            200
        );
    }

    public function generateMonthlyReport(Request $request)
    {
        // Validate and get the month parameter
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $month = $request->input('month'); // Expected format: "2024-06"
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        // 1. ClientJob data - jobs per status and income
        $jobsPerStatus = ClientJob::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $totalIncome = ClientJob::where('status', 'Completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('price');

        $averageJobPrice = ClientJob::where('status', 'Completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('price');

        // 2. Quote data - number of quotes for that month
        $totalQuotes = Quote::whereBetween('created_at', [$startDate, $endDate])->count();

        // 3. Number of quotes that turned to jobs (based on matching client data)
        $quotesToJobs = 0;
        $quotes = Quote::whereBetween('created_at', [$startDate, $endDate])->get();
        $jobs = ClientJob::whereBetween('created_at', [$startDate, $endDate])->get();

        foreach ($quotes as $quote) {
            foreach ($jobs as $job) {
                $clientData = is_array($job->client) ? $job->client : json_decode($job->client, true);
                if ($clientData &&
                    isset($clientData['email']) &&
                    $clientData['email'] === $quote->email) {
                    $quotesToJobs++;
                    break;
                }
            }
        }

        // 4. Number of unique clients that month (based on unique emails from jobs)
        $uniqueClients = ClientJob::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->map(function ($job) {
                $clientData = is_array($job->client) ? $job->client : json_decode($job->client, true);

                return $clientData['email'] ?? null;
            })
            ->filter()
            ->unique()
            ->count();

        // 5. Highest availed Service for that month
        $allServices = Service::all()->keyBy('id');
        $serviceUsage = [];

        $monthlyJobs = ClientJob::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('services')
            ->where('services', '!=', '')
            ->get();

        foreach ($monthlyJobs as $job) {
            $services = $job->services;

            // Handle different data formats
            if (is_string($services)) {
                $decoded = json_decode($services, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $services = $decoded;
                }
            }

            if (is_array($services) && ! empty($services)) {
                foreach ($services as $serviceId) {
                    $serviceId = (int) $serviceId;

                    if ($serviceId > 0 && isset($allServices[$serviceId])) {
                        $serviceName = $allServices[$serviceId]->name;
                        if (! isset($serviceUsage[$serviceName])) {
                            $serviceUsage[$serviceName] = 0;
                        }
                        $serviceUsage[$serviceName]++;
                    }
                }
            }
        }

        // Get the most used service
        $topService = null;
        $topServiceCount = 0;
        if (! empty($serviceUsage)) {
            arsort($serviceUsage);
            $topService = array_key_first($serviceUsage);
            $topServiceCount = $serviceUsage[$topService];
        }

        // 6. Highest working Member for that month
        $memberUsage = [];

        foreach ($monthlyJobs as $job) {
            $team = $job->team;

            // Handle different data formats
            if (is_string($team)) {
                $decoded = json_decode($team, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $team = $decoded;
                }
            }

            if (is_array($team) && ! empty($team)) {
                foreach ($team as $memberId) {
                    $memberId = (int) $memberId;

                    if ($memberId > 0) {
                        if (! isset($memberUsage[$memberId])) {
                            $memberUsage[$memberId] = 0;
                        }
                        $memberUsage[$memberId]++;
                    }
                }
            }
        }

        // Get the most active member
        $topMember = null;
        $topMemberJobs = 0;
        if (! empty($memberUsage)) {
            arsort($memberUsage);
            $topMemberId = array_key_first($memberUsage);
            $topMemberJobs = $memberUsage[$topMemberId];
            $topMember = \App\Models\Member::find($topMemberId);
        }

        // Compile the monthly report
        $monthlyReport = [
            'month' => $month,
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'jobs' => [
                'total_jobs' => array_sum($jobsPerStatus),
                'jobs_per_status' => $jobsPerStatus,
                'total_income' => round($totalIncome, 2),
                'average_job_price' => round($averageJobPrice, 2),
            ],
            'quotes' => [
                'total_quotes' => $totalQuotes,
                'quotes_converted_to_jobs' => $quotesToJobs,
                'conversion_rate' => $totalQuotes > 0 ? round(($quotesToJobs / $totalQuotes) * 100, 2) : 0,
            ],
            'clients' => [
                'unique_clients' => $uniqueClients,
            ],
            'services' => [
                'top_service' => $topService,
                'top_service_usage_count' => $topServiceCount,
                'all_service_usage' => $serviceUsage,
            ],
            'members' => [
                'top_member' => $topMember ? [
                    'id' => $topMember->id,
                    'name' => $topMember->name,
                    'email' => $topMember->email,
                    'jobs_worked' => $topMemberJobs,
                ] : null,
                'member_job_counts' => $memberUsage,
            ],
            'summary' => [
                'revenue_per_client' => $uniqueClients > 0 ? round($totalIncome / $uniqueClients, 2) : 0,
                'jobs_per_client' => $uniqueClients > 0 ? round(array_sum($jobsPerStatus) / $uniqueClients, 2) : 0,
            ],
        ];

        return response()->json([
            'status' => 'success',
            'monthlyReport' => $monthlyReport,
        ], 200);
    }
}
