<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submission Report - {{ $overviewData['semester']->name ?? 'N/A' }}</title>

    <style>
        /* Define Green Color Theme Variables */
        :root {
            /* Green Theme */
            --primary-green: #01a73e;     /* Main brand green */
            --dark-green: #006b2f;        /* Darker green shade */
            --light-green: #e8f5e9;       /* Very light green background */
            --accent-green: #00c853;      /* Bright accent green */
            
            /* General Text and Border */
            --dark-text: #1f2937;         /* Darker charcoal text */
            --light-text: #374151;        /* Muted gray text */
            --border-color: #d1d5db;      /* Medium gray border */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            /* Arial Font Only */
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            color: black;
            margin: 0.2in 0.5in; /* Reduced margins for landscape */
        }

        /* Landscape orientation */
        @page {
            size: landscape;
            margin: 0.2in 0.5in;
        }

        /* --- Updated Header Styling (Letterhead Style) --- */
        .header {
            width: 100%;
            margin-bottom: 15px;
            text-align: center;
            font-family: 'Arial', sans-serif;
        }

        .header-content {
            display: table;
            width: 100%;
            margin: 0;
            padding: 0;
            table-layout: fixed;
            font-family: 'Arial', sans-serif;
        }

        .logo-left {
            display: table-cell;
            vertical-align: middle;
            width: 15%; 
            text-align: right;
            font-family: 'Arial', sans-serif;
        }

        .header-center {
            display: table-cell;
            vertical-align: middle;
            width: 70%; 
            text-align: center; 
            padding: 0 10px; 
            font-family: 'Arial', sans-serif;
        }

        .logo-right {
            display: table-cell;
            vertical-align: middle;
            width: 15%; 
            text-align: left;
            font-family: 'Arial', sans-serif;
        }

        .logo {
            max-height: 70px; 
        }

        .university-info {
            margin: 0;
            padding: 0;
            line-height: 1;
            text-align: center;
            font-family: 'Arial', sans-serif;
        }

        .republic {
            font-size: 10px;
            font-weight: normal;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        .university-name {
            font-size: 14px; 
            font-weight: bold;
            margin: 2px 0 0 0; 
            text-transform: uppercase;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        .campus-name {
            font-size: 10px; 
            font-weight: bold;
            margin: 2px 0 0 0; 
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        .address {
            font-size: 9px; 
            margin: 1px 0 0 0; 
            font-family: 'Arial', sans-serif;
        }

        .contact-info {
            font-size: 9px; 
            margin: 1px 0 0 0; 
            font-family: 'Arial', sans-serif;
        }

        .website {
            font-size: 8px;
            margin: 1px 0 0 0;
            font-style: italic;
            font-family: 'Arial', sans-serif;
        }

        .college-name {
            font-size: 12px;
            font-weight: bold;
            margin: 5px 0 0 0;
            text-transform: uppercase;
            white-space: nowrap;
            font-family: 'Arial', sans-serif;
        }

        .college-divider {
            width: 100%;
            height: 2px;
            background-color: black;
            margin: 8px 0 0 0;
            border: none;
        }

        .report-title {
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0 0 0;
            text-transform: uppercase;
            font-family: 'Arial', sans-serif;
        }

        /* Report Info */
        .report-info {
            margin: 10px 0;
            padding: 8px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-family: 'Arial', sans-serif;
        }

        .report-info-item {
            margin: 2px 0;
            font-size: 9px;
            font-family: 'Arial', sans-serif;
        }

        /* Table Styling - Matching Report Index */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 8px;
            font-family: 'Arial', sans-serif;
            table-layout: auto;
        }

        .report-table th {
            background-color: #e8f5e9;
            border: 1px solid #16A34A;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-family: 'Arial', sans-serif;
        }

        .report-table td {
            border: 1px solid #d1d5db;
            padding: 5px 4px;
            vertical-align: top;
            font-family: 'Arial', sans-serif;
        }

        .user-column, .program-column, .course-column {
            background-color: white;
            font-family: 'Arial', sans-serif;
            width: auto !important;
            max-width: 150px;
            white-space: nowrap;
        }

        .user-column {
            font-weight: bold;
        }

        .program-column, .course-column {
            font-weight: 500;
        }

        /* Updated Badge Styles with Full Border Radius */
        .status-submitted {
            background-color: #d1fae5;
            color: #065f46;
            padding: 2px 6px;
            border-radius: 9999px; /* Full border radius */
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
            font-family: 'Arial', sans-serif;
        }

        .status-not-submitted {
            background-color: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 9999px; /* Full border radius */
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
            font-family: 'Arial', sans-serif;
        }

        .status-not-applicable {
            background-color: #f3f4f6;
            color: #6b7280;
            padding: 2px 6px;
            border-radius: 9999px; /* Full border radius */
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
            font-family: 'Arial', sans-serif;
        }

        .summary-grid {
            display: table;
            width: 100%;
            margin: 10px 0;
            border: 1px solid #16A34A;
            border-collapse: collapse;
            font-family: 'Arial', sans-serif;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 8px 4px;
            text-align: center;
            border-right: 1px solid #16A34A;
            font-family: 'Arial', sans-serif;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-name {
            font-size: 8px;
            font-weight: 700;
            color: var(--dark-text);
            text-transform: uppercase;
            font-family: 'Arial', sans-serif;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 2px;
            color: var(--primary-green);
            font-family: 'Arial', sans-serif;
        }

        /* Footer Styling - Reduced border weight */
        .footer {
            width: 100%;
            margin-top: 35px;
            padding-top: 12px;
            border-top: 1px solid #1f2937; /* Reduced from 2px to 1px */
            font-family: 'Arial', sans-serif;
        }

        .footer-content {
            display: table;
            width: 100%;
            font-family: 'Arial', sans-serif;
        }

        .footer-left {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
            text-align: left;
            font-family: 'Arial', sans-serif;
        }

        .footer-right {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
            text-align: right;
            color: var(--light-text);
            font-family: 'Arial', sans-serif;
        }

        .footer-info {
            font-size: 11px;
            font-family: 'Arial', sans-serif;
        }

        /* Page breaks */
        .page-break {
            page-break-before: always;
        }

        /* Prevent rows from breaking */
        .no-break {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .user-block {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Compact styling for landscape */
        .compact-text {
            padding-bottom: 2px;
            font-size: 8px;
            font-family: 'Arial', sans-serif;
        }

        .small-text {
            font-weight: normal;
            font-size: 7px;
            font-family: 'Arial', sans-serif;
        }

        /* No data message */
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
            font-family: 'Arial', sans-serif;
        }

        /* Row styling */
        .data-row {
            border-bottom: 1px solid #e5e7eb;
        }

        .data-row:hover {
            background-color: #f9fafb;
        }

        /* Empty user column for continued courses */
        .empty-user-column {
            /* Keep the background white to match the row */
            background-color: white; 
            border-left: 1px solid #d1d5db;
            border-right: 1px solid #d1d5db;
            /* Ensure it has minimum padding/height */
            padding: 5px 4px;
        }

        /* NEW: Right-aligned stacked approval sections */
        .approval-sections-right {
            width: 100%;
            margin-top: 40px;
            font-family: 'Arial', sans-serif;
            text-align: right;
        }

        .approval-item {
            margin-bottom: 30px;
            text-align: right;
            width: 100%;
            display: block;
        }

        .approval-item:last-child {
            margin-bottom: 0;
        }

        .approval-label {
            font-size: 11px;
            margin-bottom: 0;
            font-family: 'Arial', sans-serif;
            font-weight: bold;
            text-align: right;
            width: 100%;
            display: block;
        }

        .approval-spacing {
            height: 25px; /* Space between label and name */
            width: 100%;
            display: block;
        }

        .approval-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2px;
            font-family: 'Arial', sans-serif;
            text-align: right;
            width: 100%;
            display: block;
        }

        .approval-position {
            font-size: 11px;
            font-family: 'Arial', sans-serif;
            text-align: right;
            width: 100%;
            display: block;
        }

        /* NEW: Force table header to repeat on each page */
        .report-table thead {
            display: table-header-group;
        }

        /* NEW: Improved page break handling for user blocks */
        .user-course-block {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Style for continuation rows (no longer needed to hide the first cell, but kept for context) */
        .continued-user-row {
            /* Removed the user-column specific styling as it's now explicitly an empty cell */
        }

        @media print {
            .approval-sections-right {
                page-break-inside: avoid !important;
                page-break-before: avoid !important;
                margin-top: 30px;
                text-align: right;
            }
        }
    </style>
</head>

<body>
    @php
        $pageNumber = 1;
        $totalPages = 1; 

        use App\Models\Signatory;
        $deanSignatory = Signatory::where('position', 'like', '%dean%')
            ->where('is_active', true)
            ->first();

    @endphp

    <div class="header">
        <div class="header-content">
            <div class="logo-left">
                @if(file_exists(public_path('images/sample.png')))
                    <img src="{{ public_path('images/sample.png') }}" alt="CVSU Logo" class="logo">
                @else
                    <div style="height: 70px; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 8px;">[CVSU LOGO]</span>
                    </div>
                @endif
            </div>
            <div class="header-center">
                <div class="university-info">
                    <div class="republic">Republic of the Philippines</div>
                    <div class="university-name">CAVITE STATE UNIVERSITY</div>
                    <div class="campus-name">Don Severino de las Alas Campus</div>
                    <div class="address">Indang, Cavite</div>
                    <div class="contact-info">(046) 483-9250</div>
                    <div class="website">www.cvsu.edu.ph</div>
                </div>

                <div class="college-name">GRADUATE SCHOOL AND OPEN LEARNING COLLEGE</div>
            </div>
            <div class="logo-right">
                @if(file_exists(public_path('images/1.png')))
                    <img src="{{ public_path('images/1.png') }}" alt="BP Logo" class="logo">
                @else
                    <div style="height: 70px; display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 8px;">[BP LOGO]</span>
                    </div>
                @endif
            </div>
        </div>

        <hr class="college-divider">
        
        <div class="report-title">SUBMISSION REPORT - {{ $overviewData['semester']->name ?? 'N/A' }}</div>
    </div>

    @php
        $reportController = new \App\Http\Controllers\Admin\ReportController();
        // Assuming calculateSummaryStatistics is a static method or accessible this way
        $summaryStats = method_exists($reportController, 'calculateSummaryStatistics') 
                        ? $reportController->calculateSummaryStatistics($overviewData) 
                        : ['totalCourses' => 0, 'totalActualSubmissions' => 0, 'totalPossibleSubmissions' => 0, 'submissionRate' => 0];
    @endphp
    
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-name">Total Faculty</div>
            <div class="summary-value">{{ $overviewData['users']->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Total Courses</div>
            <div class="summary-value">{{ $summaryStats['totalCourses'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Submissions</div>
            <div class="summary-value">{{ $summaryStats['totalActualSubmissions'] }}/{{ $summaryStats['totalPossibleSubmissions'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Completion Rate</div>
            <div class="summary-value">{{ $summaryStats['submissionRate'] }}%</div>
        </div>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th style="text-align: center;">User</th>
                <th style="text-align: center;">Program</th>
                <th style="text-align: center;">Course</th>
                @foreach($overviewData['requirements'] as $requirement)
                    <th style="text-align: center;">
                        <div class="truncate">
                            {{ \Illuminate\Support\Str::limit($requirement->name, 20) }}
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($overviewData['users'] as $userIndex => $user)
                @php
                    $courses = $overviewData['userCoursesData'][$user->id] ?? collect();
                    $hasCourses = $courses->isNotEmpty();
                    $formattedMiddleName = $user->middlename ? substr(trim($user->middlename), 0, 1) . '.' : '';
                @endphp
                
                @if($hasCourses)
                    @foreach($courses as $courseIndex => $course)
                        <tr class="data-row no-break user-course-block">
                            @if($courseIndex === 0)
                                <td class="user-column">
                                    <div class="compact-text" style="font-weight: bold;">
                                        {{ $user->firstname }} {{ $formattedMiddleName }} {{ $user->lastname }} {{ $user->extensionname }}
                                    </div>
                                    <div class="small-text">
                                        {{ $user->position }}
                                    </div>
                                    <div class="small-text">{{ $user->email }}</div>
                                </td>
                            @else
                                <td class="user-column empty-user-column">
                                    </td>
                            @endif
                            
                            <td style="text-align: center;">
                                {{ $course->program->program_code ?? 'N/A' }}
                            </td>
                            
                            <td style="text-align: center;">
                                <div class="compact-text">{{ $course->course_code }}</div>
                            </td>
                            
                            @foreach($overviewData['requirements'] as $requirement)
                                @php
                                    // Check if the course is assigned to the requirement
                                    $isAssigned = $reportController->isCourseAssignedToRequirement($course, $requirement);
                                    
                                    if (!$isAssigned) {
                                        $displayText = 'N/A';
                                        $badgeClass = 'status-not-applicable';
                                    } else {
                                        $hasSubmitted = $reportController->hasUserSubmittedForCourse($user->id, $requirement->id, $course->id, $overviewData['submissionIndicators']);
                                        $displayText = $hasSubmitted ? 'Sub' : 'No Sub';
                                        $badgeClass = $hasSubmitted ? 'status-submitted' : 'status-not-submitted';
                                    }
                                @endphp
                                <td style="text-align: center;">
                                    <span class="{{ $badgeClass }}">
                                        {{ $displayText }}
                                    </span>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @else
                    <tr class="data-row no-break">
                        <td class="user-column">
                            <div class="compact-text" style="font-weight: bold;">{{ $user->firstname }} {{ $formattedMiddleName }} {{ $user->lastname }} {{ $user->extensionname }}</div>
                            @if($user->position)
                                <div class="small-text" style="font-weight: 500;">{{ $user->position }}</div>
                            @endif
                            <div class="small-text">{{ $user->email }}</div>
                        </td>
                        
                        <td class="program-column" >
                            <span class="small-text" style="color: #9ca3af;">No program</span>
                        </td>
                        
                        <td class="course-column">
                            <span class="small-text" style="color: #9ca3af;">No course</span>
                        </td>
                        
                        @foreach($overviewData['requirements'] as $requirement)
                            <td style="text-align: center;">
                                <span class="status-not-applicable">
                                    N/A
                                </span>
                            </td>
                        @endforeach
                    </tr>
                @endif
                
            @empty
                <tr>
                    <td colspan="{{ 3 + max(1, $overviewData['requirements']->count()) }}" class="no-data">
                        No faculty members found matching the selected criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @php
        $preparedByMiddleName = Auth::user()->middlename ? substr(trim(Auth::user()->middlename), 0, 1) . '.' : '';
    @endphp

    <div class="footer">
        <div class="footer-content">
            <div class="footer-right">
                <div class="footer-info">Generated On: {{ now()->format('l, F j, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Approval Sections aligned to the right -->
    <div class="approval-sections-right">
        <div class="approval-item">
            <div class="approval-label">Prepared by:</div>
            <div class="approval-spacing"></div>
            <div class="approval-name">{{ Auth::user()->firstname }} {{ $preparedByMiddleName }} {{ Auth::user()->lastname }} {{ Auth::user()->extensionname }}</div>
            <div class="approval-position">{{ Auth::user()->position ?? 'Administrator' }}</div>
        </div>
        
        <div class="approval-item">
            <div class="approval-label">Approved by:</div>
            <div class="approval-spacing"></div>
            <div class="approval-name">
                {{ $deanSignatory->name ?? 'Dr. Bettina Joyce P. Ilagan' }}
            </div>
            <div class="approval-position">
                {{ $deanSignatory->position ?? 'Dean, Graduate School and Open Learning College' }}
            </div>
        </div>
    </div>
</body>
</html>