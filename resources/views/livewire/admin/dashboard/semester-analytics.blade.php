<div>
    <!-- Loading State -->
    <div wire:loading.delay class="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center gap-3 shadow-xl">
            <span class="loading loading-spinner loading-md text-green-600"></span>
            <span class="text-gray-700">Loading analytics...</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" wire:loading.class="opacity-50 pointer-events-none">
        <!-- User Activity Metrics -->
        <div class="card bg-white shadow-lg hover:shadow-xl transition-shadow duration-300 border-0">
            <div class="card-body">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h4 class="card-title text-xl font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            User Activity Metrics
                        </h4>
                        <p class="text-sm text-gray-500 mt-1">Top performing users by completion rate</p>
                    </div>
                    @if(count($userActivityStats) > 0)
                        <div class="badge badge-success badge-outline">
                            {{ count($userActivityStats) }} active users
                        </div>
                    @endif
                </div>
                
                @if(count($userActivityStats) > 0)
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-100">
                                    <th class="text-left font-semibold text-gray-700 bg-gray-50 rounded-l-lg">
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            User
                                        </div>
                                    </th>
                                    <th class="text-center font-semibold text-gray-700 bg-gray-50">
                                        <div class="flex items-center justify-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            Submitted
                                        </div>
                                    </th>
                                    <th class="text-center font-semibold text-gray-700 bg-gray-50">
                                        <div class="flex items-center justify-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Approved
                                        </div>
                                    </th>
                                    <th class="text-center font-semibold text-gray-700 bg-gray-50">
                                        <div class="flex items-center justify-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            Total Reqs
                                        </div>
                                    </th>
                                    <th class="text-center font-semibold text-gray-700 bg-gray-50 rounded-r-lg">
                                        <div class="flex items-center justify-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                            Completion
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userActivityStats as $index => $user)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150 {{ $index === 0 ? 'bg-green-50' : '' }}">
                                        <td class="font-medium text-gray-900">
                                            <div class="flex items-center gap-2">
                                                @if($index === 0)
                                                    <div class="w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center">
                                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <span>{{ $user['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-outline badge-primary">{{ $user['submitted'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-outline badge-success">{{ $user['approved'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-outline badge-neutral">{{ $user['total_requirements'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <div class="radial-progress text-green-600" 
                                                    style="--value:{{ $user['completion_rate'] }}; 
                                                        --size:3rem;
                                                        --thickness: 6px;">
                                                    <span class="text-xs font-bold">{{ $user['completion_rate'] }}%</span>
                                                </div>
                                                @if($user['completion_rate'] >= 80)
                                                    <div class="tooltip" data-tip="Excellent performance!">
                                                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Summary Stats -->
                    <div class="mt-6 grid grid-cols-3 gap-4 p-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ collect($userActivityStats)->avg('completion_rate') ? number_format(collect($userActivityStats)->avg('completion_rate'), 1) : '0' }}%</div>
                            <div class="text-xs text-gray-600">Avg Completion</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ collect($userActivityStats)->sum('submitted') }}</div>
                            <div class="text-xs text-gray-600">Total Submitted</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ collect($userActivityStats)->sum('approved') }}</div>
                            <div class="text-xs text-gray-600">Total Approved</div>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                        </div>
                        <h5 class="text-lg font-semibold text-gray-900 mb-2">No User Activity Data</h5>
                        <p class="text-gray-500 max-w-sm">No user activity data found for this semester. Users may not have submitted any requirements yet.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Storage Usage Chart -->
        <div class="card bg-white shadow-lg hover:shadow-xl transition-shadow duration-300 border-0">
            <div class="card-body">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h4 class="card-title text-xl font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                            </svg>
                            Storage Usage
                        </h4>
                        <p class="text-sm text-gray-500 mt-1">File types and storage distribution</p>
                    </div>
                    @if($totalStorage > 0)
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900">{{ $this->formatBytes($totalStorage) }}</div>
                            <div class="text-xs text-gray-500">Total Usage</div>
                        </div>
                    @endif
                </div>
                
                @if(count($storageStats) > 0)
                    <div class="h-64" wire:ignore>
                        <canvas 
                            id="storageUsageChart"
                            x-data="{
                                init() {
                                    const ctx = this.$refs.canvas.getContext('2d');
                                    const mimeTypes = @js(array_keys($storageStats));
                                    const humanizedTypes = mimeTypes.map(type => {
                                        // Convert MIME types to more readable format
                                        const typeMap = {
                                            'application/pdf': 'PDF',
                                            'application/msword': 'Word',
                                            'application/vnd.ms-excel': 'Excel',
                                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'Word (DOCX)',
                                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'Excel (XLSX)',
                                            'image/jpeg': 'JPEG',
                                            'image/png': 'PNG',
                                            'image/gif': 'GIF',
                                            'text/plain': 'Text',
                                            'application/zip': 'ZIP'
                                        };
                                        return typeMap[type] || type;
                                    });

                                    const fontFamily = 'Figtree, ui-sans-serif, system-ui, sans-serif, Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji';

                                    new Chart(ctx, {
                                        type: 'pie',
                                        data: {
                                            labels: humanizedTypes,
                                            datasets: [{
                                                data: @js(array_values($storageStats)),
                                                backgroundColor: [
                                                    '#4CAF50', '#8BC34A', '#CDDC39', 
                                                    '#FFC107', '#2196F3', '#4CAF50', 
                                                    '#8BC34A', '#CDDC39', '#FFC107'
                                                ],
                                                borderWidth: 1
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            layout: {
                                                padding: {
                                                }
                                            },
                                            plugins: {
                                                legend: {
                                                    display: true,
                                                    position: 'left',
                                                    align: 'center',
                                                    labels: {
                                                        boxWidth: 12,
                                                        padding: 20,
                                                        font: {
                                                            family: fontFamily,
                                                            size: 13
                                                        },
                                                        generateLabels: function(chart) {
                                                            const data = chart.data;
                                                            if (data.labels.length && data.datasets.length) {
                                                                return data.labels.map(function(label, i) {
                                                                    const meta = chart.getDatasetMeta(0);
                                                                    const style = meta.controller.getStyle(i);
                                                                    
                                                                    return {
                                                                        text: label + ' (' + data.datasets[0].data[i] + ' bytes)',
                                                                        fillStyle: style.backgroundColor,
                                                                        strokeStyle: style.borderColor,
                                                                        lineWidth: style.borderWidth,
                                                                        hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                                                                        index: i
                                                                    };
                                                                });
                                                            }
                                                            return [];
                                                        }
                                                    }
                                                },
                                                tooltip: {
                                                    callbacks: {
                                                        label: (context) => {
                                                            const total = {{ array_sum($storageStats) }};
                                                            const value = context.raw;
                                                            const percentage = Math.round((value / total) * 100);
                                                            return `${humanizedTypes[context.dataIndex]}: ${context.raw} bytes (${percentage}%)`;
                                                        }
                                                    },
                                                    bodyFont: {
                                                        family: fontFamily
                                                    },
                                                    titleFont: {
                                                        family: fontFamily
                                                    }
                                                }
                                            }
                                        }
                                    });
                                }
                            }"
                            x-ref="canvas"
                        ></canvas>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h5 class="text-lg font-semibold text-gray-900 mb-2">No Storage Data</h5>
                        <p class="text-gray-500 max-w-sm">No files have been uploaded for this semester yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>