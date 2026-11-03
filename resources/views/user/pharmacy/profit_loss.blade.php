@extends('layouts.pharmacy')

@section('title')
    {{ __('Profit & Loss Dashboard') }}
@stop

@section('content')
<div class="p-6 space-y-6 bg-gray-50 dark:bg-gray-900 min-h-screen">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-chart-line text-primary-600"></i>
                {{__("Profit & Loss Analysis")}}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{__("Track your business costs, sales, gross profit, and overall performance margins.")}}
            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg px-4 py-2 border dark:border-gray-700">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{__("Current Currency")}}</span>
            <div class="text-lg font-bold text-gray-800 dark:text-gray-200">{{ $setting->currency ?? 'USD' }}</div>
        </div>
    </div>

    {{-- Financial Summary Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue Card -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6 border-l-4 border-green-500 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{__("Total Revenue")}}</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">
                        {{ $setting->currency }}{{ number_format($totalRevenue, 2) }}
                    </h3>
                </div>
                <div class="p-3 rounded-full bg-green-50 dark:bg-green-900/30 text-green-500">
                    <i class="fas fa-hand-holding-usd text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full">
                    <div class="bg-green-500 h-1.5 rounded-full" style="width: 100%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{__("Total sales revenue from completed orders")}}</p>
            </div>
        </div>

        <!-- COGS Card -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6 border-l-4 border-red-500 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{__("Cost of Goods Sold")}}</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">
                        {{ $setting->currency }}{{ number_format($totalCogs, 2) }}
                    </h3>
                </div>
                <div class="p-3 rounded-full bg-red-50 dark:bg-red-900/30 text-red-500">
                    <i class="fas fa-tags text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full">
                    <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $totalRevenue > 0 ? min(100, ($totalCogs / $totalRevenue) * 100) : 0 }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{__("Buying cost value of items sold")}}</p>
            </div>
        </div>

        <!-- Gross Profit Card -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6 border-l-4 border-blue-500 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{__("Gross Profit")}}</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">
                        {{ $setting->currency }}{{ number_format($grossProfit, 2) }}
                    </h3>
                </div>
                <div class="p-3 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-500">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full">
                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $totalRevenue > 0 ? min(100, ($grossProfit / $totalRevenue) * 100) : 0 }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{__("Gross Profit generated")}}</p>
            </div>
        </div>

        <!-- Profit Margin Card -->
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6 border-l-4 border-purple-500 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{__("Profit Margin")}}</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">
                        {{ number_format($profitMargin, 1) }}%
                    </h3>
                </div>
                <div class="p-3 rounded-full bg-purple-50 dark:bg-purple-900/30 text-purple-500">
                    <i class="fas fa-percentage text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full">
                    <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ min(100, $profitMargin) }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{__("Average gross profit percentage margin")}}</p>
            </div>
        </div>
    </div>

    {{-- Charts & Monthly Trends --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Revenue Trend Chart --}}
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6 lg:col-span-2 border dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-chart-area text-primary-500 mr-2"></i>{{__("Monthly Sales & Profit Trend")}}</h3>
            <div class="h-64 relative">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        {{-- Right: Key Business Analytics --}}
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6 border dark:border-gray-700 flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-chart-pie text-secondary-500 mr-2"></i>{{__("Operational Insights")}}</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{__("Total Completed Sales")}}</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ count($orderMargins) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{__("Average Order Value")}}</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">
                            {{ $setting->currency }}{{ count($orderMargins) > 0 ? number_format($totalRevenue / count($orderMargins), 2) : '0.00' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b dark:border-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{__("Average Cost per Order")}}</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">
                            {{ $setting->currency }}{{ count($orderMargins) > 0 ? number_format($totalCogs / count($orderMargins), 2) : '0.00' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="mt-6 p-4 rounded-lg bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-900">
                <h4 class="font-semibold text-primary-800 dark:text-primary-200 text-sm mb-1"><i class="fas fa-info-circle mr-1"></i> {{__("Profitability Advisory")}}</h4>
                <p class="text-xs text-primary-700 dark:text-primary-300 leading-relaxed">
                    {{__("Maintain a target profit margin above 30% by updating accurate medicine cost prices in your inventory manager to track true performance stats.")}}
                </p>
            </div>
        </div>
    </div>

    {{-- Order Breakdown Ledger --}}
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-6 border dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4"><i class="fas fa-list-alt text-primary-500 mr-2"></i>{{__("Completed Sales & Profit Ledger")}}</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wider">{{__("Order ID")}}</th>
                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wider">{{__("Date & Time")}}</th>
                        <th class="px-6 py-3 text-right font-semibold uppercase tracking-wider">{{__("Revenue")}}</th>
                        <th class="px-6 py-3 text-right font-semibold uppercase tracking-wider">{{__("COGS (Cost)")}}</th>
                        <th class="px-6 py-3 text-right font-semibold uppercase tracking-wider">{{__("Gross Profit")}}</th>
                        <th class="px-6 py-3 text-right font-semibold uppercase tracking-wider">{{__("Margin")}}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200">
                    @forelse($orderMargins as $margin)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-4 font-medium">#{{ $margin['id'] }}</td>
                        <td class="px-6 py-4">{{ $margin['date'] }}</td>
                        <td class="px-6 py-4 text-right">{{ $setting->currency }}{{ number_format($margin['revenue'], 2) }}</td>
                        <td class="px-6 py-4 text-right text-red-500">{{ $setting->currency }}{{ number_format($margin['cogs'], 2) }}</td>
                        <td class="px-6 py-4 text-right text-green-500 font-semibold">{{ $setting->currency }}{{ number_format($margin['profit'], 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $margin['margin'] >= 30 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' }}">
                                {{ number_format($margin['margin'], 1) }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            {{__("No completed orders registered for this pharmacy yet.")}}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('footer')
    {{-- Pull in ChartJS from CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
            
            // Monthly stats from controller
            const monthlyData = @json($monthlyStats);
            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            
            const labels = monthlyData.map(item => {
                return months[item.month - 1] + ' ' + item.year;
            });
            const revenues = monthlyData.map(item => parseFloat(item.revenue));

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels.length > 0 ? labels : ["No Data Available"],
                    datasets: [{
                        label: 'Monthly Sales Revenue',
                        data: revenues.length > 0 ? revenues : [0],
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.1)',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(156, 163, 175, 0.15)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        });
    </script>
@stop
@stop
