<div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- User Activity Metrics -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="card-title">User Activity Metrics</h4>
                </div>
                
                @if(count($userActivityStats) > 0)
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Submitted</th>
                                    <th>Approved</th>
                                    <th>Total Requirements</th>
                                    <th>Completion Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userActivityStats as $user)
                                    <tr>
                                        <td>{{ $user['name'] }}</td>
                                        <td>{{ $user['submitted'] }}</td>
                                        <td>{{ $user['approved'] }}</td>
                                        <td>{{ $user['total_requirements'] }}</td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div class="radial-progress" 
                                                    style="--value:{{ $user['completion_rate'] }}; 
                                                        --size:4rem;
                                                        --thickness: 8px;
                                                        color: rgba(55, 120, 64, 1);">
                                                    {{ $user['completion_rate'] }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">No user activity data found for this semester.</p>
                @endif
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="card-title">Storage Usage</h4>
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
                                            // Add more mappings as needed
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
                    <div class="mt-4 text-center">
                        <span class="font-semibold">Total:</span> {{ $this->formatBytes($totalStorage) }}
                    </div>
                @else
                    <p class="text-gray-500">No storage data found for this semester.</p>
                @endif
            </div>
        </div>
    </div>
</div>