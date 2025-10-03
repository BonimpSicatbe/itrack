<div>
    <div class="gap-6">
        <!-- User Activity Metrics -->
        <div class="card bg-white shadow-lg hover:shadow-xl transition-shadow duration-300 border-0 rounded-xl">
            <div class="card-body">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h4 class="card-title text-xl font-bold text-gray-800 flex items-center gap-2">
                            User Activity Metrics
                        </h4>
                        <p class="text-sm text-gray-500 mt-1">Top performing users by completion rate</p>
                    </div>
                </div>
                
                @if(count($userActivityStats) > 0)
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-100">
                                    <th class="text-left font-semibold text-gray-700 bg-gray-50 rounded-l-lg">User</th>
                                    <th class="text-center font-semibold text-gray-700 bg-gray-50">Submitted</th>
                                    <th class="text-center font-semibold text-gray-700 bg-gray-50">Approved</th>
                                    <th class="text-center font-semibold text-gray-700 bg-gray-50">Total Reqs</th>
                                    <th class="text-center font-semibold text-gray-700 bg-gray-50 rounded-r-lg">Completion</th>
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
                                        <td class="text-center font-semibold">{{ $user['submitted'] }}</td>
                                        <td class="text-center font-semibold">{{ $user['approved'] }}</td>
                                        <td class="text-center font-semibold">{{ $user['total_requirements'] }}</td>
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
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                        </div>
                        <h5 class="text-lg font-semibold text-gray-800 mb-2">No User Activity Data</h5>
                        <p class="text-gray-500 max-w-sm">No user activity data found for this semester.</p>
                    </div>
                @endif
            </div>
        </div>

        
    </div>
</div>