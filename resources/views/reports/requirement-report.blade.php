<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Requirement Report</title>

    <style>
        /* Define Green Color Theme Variables */
        :root {
            /* Green Theme */
            --primary-green: #01a73e;     /* Main brand green */
            --dark-green: #006b2f;        /* Darker green shade */
            --light-green: #e8f5e9;       /* Very light green background */
            --accent-green: #00c853;      /* Bright accent green */
            
            /* General Text and Border */
            --border-color: #d1d5db;      /* Medium gray border */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            /* Arial Font Only - Applied globally */
            font-family: 'Arial', sans-serif !important;
            font-size: 12px;
            color: black;
            margin: 0.4in 1in;
        }

        /* Force Arial on all elements */
        body, div, span, table, th, td, tr, strong, .course-info, .course-code, .status, .submission-date {
            font-family: 'Arial', sans-serif !important;
        }

        /* --- Updated Header Styling (Letterhead Style) --- */
        .header {
            width: 100%;
            margin-bottom: 25px;
            text-align: center;
        }

        .header-content {
            display: table;
            width: 100%;
            margin: 0;
            padding: 0;
            table-layout: fixed;
        }

        .logo-left {
            display: table-cell;
            vertical-align: right;
            width: 25%; 
            text-align: right;
        }

        .header-center {
            display: table-cell;
            vertical-align: middle;
            width: 50%; 
            text-align: center; 
            padding: 0 1px; 
        }

        .logo-right {
            display: table-cell;
            vertical-align: left;
            width: 25%; 
            text-align: left;
            padding-left: 13px; 
        }

        .logo {
            max-height: 100px; 
            padding-top: 5px;
        }

        .university-info {
            margin: 0;
            padding: 0;
            line-height: 0.9;
            text-align: center;
        }

        .republic {
            font-size: 12px;
            font-weight: normal;
            margin: 0;
            padding: 0;
        }

        .university-name {
            font-size: 17px; 
            font-weight: bold;
            margin: 4px 0 0 0; 
            text-transform: uppercase;
            padding: 0;
        }

        .campus-name {
            font-size: 12px; 
            font-weight: bold;
            margin: 5px 0 0 0; 
            padding: 0;
        }

        .address {
            font-size: 12px; 
            margin: 4px 0 0 0; 
        }

        .contact-info {
            font-size: 12px; 
            margin: 3px 0 0 0; 
        }

        .website {
            font-size: 12px;
            margin: 3px 0 0 0;
            font-style: italic;
        }

        .college-name {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0 0 0;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .college-divider {
            width: 100%;
            height: 2px;
            background-color: black;
            margin: 15px 0 0 0;
            border: none;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin: 18px 0 0 0;
            text-transform: uppercase;
        }

        /* Footer Styling */
        .footer {
            width: 100%;
            margin-top: 35px;
            padding-top: 12px;
            border-top: 2px solid black;
        }

        .footer-content {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
            text-align: left;
        }

        .footer-right {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
            text-align: right;
            color: #666;
        }

        .footer-logo {
            max-width: 120px;
            height: auto;
        }

        .footer-info {
            font-size: 11px;
        }

        /* --- Section Titles (Minimalist) --- */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: black;
            margin: 30px 0 12px 0;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 6px;
            page-break-after: avoid;
            letter-spacing: 0.8px;
        }

        /* --- Summary Grid (Inline) --- */
        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 18px;
            border: 1px solid #16A34A;
            overflow: hidden;
            border-collapse: collapse;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 12px 6px;
            text-align: center;
            border-right: 1px solid #16A34A;
            border-radius: 4px;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-name {
            font-size: 11px;
            font-weight: 700;
            color: black;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 20px;
            font-weight: bold;
            margin-top: 4px;
            color: var(--primary-green);
        }

        /* --- Instructor/Course Table Styling --- */
        .instructor-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            page-break-inside: auto;
            font-family: 'Arial', sans-serif !important;
        }

        .instructor-header {
            background-color: var(--light-green);
            font-size: 12px;
            font-weight: bold;
            color: var(--primary-green);
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        .instructor-header th {
             padding: 10px 12px;
             text-align: left;
             font-family: 'Arial', sans-serif !important;
        }

        .instructor-row {
            border-bottom: 1px solid var(--border-color);
            font-family: 'Arial', sans-serif !important;
        }

        .instructor-row td {
            padding: 12px;
            vertical-align: top;
            font-size: 11px;
            font-family: 'Arial', sans-serif !important;
        }

        .course-row {
            background-color: #ffffff;
        }

        .course-row:nth-child(even) {
            background-color: #f9fafb;
        }

        .course-info {
            font-weight: 600;
            color: black;
            font-family: 'Arial', sans-serif !important;
        }

        .course-code {
            font-weight: bold;
            color: var(--primary-green);
            font-size: 11px;
            font-family: 'Arial', sans-serif !important;
        }

        .course-name {
            font-size: 11px;
            color: black;
            margin: 2px 0;
            font-family: 'Arial', sans-serif !important;
        }

        .program-info {
            font-size: 10px;
            color: #666;
            margin-top: 4px;
            font-family: 'Arial', sans-serif !important;
        }

        /* --- Status Styling --- */
        .status {
            text-transform: uppercase;
            font-size: 10px;
            padding: 5px 9px;
            border-radius: 3px;
            display: inline-block;
            font-weight: bold;
            letter-spacing: 0.5px;
            white-space: nowrap;
            font-family: 'Arial', sans-serif !important;
        }

        .status-submitted { 
            background-color: #d1fae5; 
            color: #065f46; 
        }
        
        .status-no-submission { 
            background-color: #f3f4f6; 
            color: #6b7280; 
        }

        .submission-date {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
            display: block;
            font-family: 'Arial', sans-serif !important;
        }

        .no-data {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 25px;
            border: 1px dashed var(--border-color);
            background-color: #fafbfc;
            font-family: 'Arial', sans-serif !important;
        }

        /* Alternative Course Display Styles */
        .course-display-compact {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .course-main-line {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .course-code-compact {
            font-weight: bold;
            color: var(--primary-green);
            font-size: 11px;
        }

        .course-name-compact {
            font-size: 11px;
            color: black;
        }

        /* Print Specifics */
        @media print {
            body {
                margin: 0.5in 1in;
                font-family: 'Arial', sans-serif !important;
            }
            
            .header { border-bottom: none; }
            .footer { border-top: 2px solid black; }
            
            .instructor-header, .instructor-row, .summary-item {
                 -webkit-print-color-adjust: exact;
                 color-adjust: exact;
            }
            
            .instructor-header {
                 background-color: #cccccc !important;
                 color: black !important;
                 border-top: 1px solid black;
                 border-bottom: 1px solid black;
            }

            .course-row:nth-child(even) {
                 background-color: #f5f5f5 !important;
            }

            .summary-item {
                 border: 1px solid black;
            }
            
            .status {
                border: 1px solid #333 !important;
                background-color: #fff !important;
                color: black !important;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-content">
            <div class="logo-left">
                <img src="{{ public_path('images/sample.png') }}" alt="CVSU Logo" class="logo">
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

                <br>
                
                <div class="college-name">GRADUATE SCHOOL AND OPEN LEARNING COLLEGE</div>
            </div>
            <div class="logo-right">
                <img src="{{ public_path('images/1.png') }}" alt="BP Logo" class="logo">
            </div>
        </div>

        <hr class="college-divider">
        
        <div class="report-title">{{ $requirement->name }} - SUBMISSION REPORT</div>
    </div>

    <div class="section-title">REPORT OVERVIEW</div>
    
    @php
        // Calculate statistics based on available data
        $allInstructors = collect([]);
        $totalCourseAssignments = 0;
        $submittedCount = 0;
        $instructorsWithCourses = [];
        
        // Get all unique instructors from submitted requirements and not submitted users
        $submittedInstructors = $submittedUsers->pluck('user')->unique('id');
        $notSubmittedInstructors = $notSubmittedUsers->unique('id');
        
        $allInstructors = $submittedInstructors->merge($notSubmittedInstructors)->unique('id');
        $totalInstructors = $allInstructors->count();
        
        // Calculate total course assignments and submissions
        foreach($allInstructors as $instructor) {
            // Get assigned courses for this instructor from the courseAssignments relationship
            $instructorCourses = $instructor->courseAssignments
                ->where('semester_id', $semester->id)
                ->pluck('course')
                ->filter(); // Remove null values
            
            $courseSubmissions = [];
            
            foreach($instructorCourses as $course) {
                if (!$course) continue;
                
                $totalCourseAssignments++;
                
                $submission = $submittedUsers->where('user_id', $instructor->id)
                                            ->where('course_id', $course->id)
                                            ->first();
                
                if ($submission) {
                    $submittedCount++;
                }
                
                $courseSubmissions[] = [
                    'course' => $course,
                    'submission' => $submission
                ];
            }
            
            if (count($courseSubmissions) > 0) {
                $instructorsWithCourses[] = [
                    'instructor' => $instructor,
                    'courseSubmissions' => $courseSubmissions
                ];
            }
        }
        
        $noSubmissionCount = $totalCourseAssignments - $submittedCount;
        $completionRate = $totalCourseAssignments > 0 ? round(($submittedCount / $totalCourseAssignments) * 100, 1) : 0;
    @endphp
    
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-name">Total Faculty</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $totalInstructors }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Submitted</div>
            <div class="summary-value" style="color: var(--primary-green);">{{ $submittedCount }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">No Submission</div>
            <div class="summary-value" style="color: var(--primary-green);">{{ $noSubmissionCount }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Completion Rate</div>
            <div class="summary-value" style="color: var(--accent-green);">
                {{ $completionRate }}%
            </div>
        </div>
    </div>

    <div class="section-title">SEMESTER & REQUIREMENT DETAILS</div>
    <div style="margin-bottom: 25px; font-size: 12px;">
        <div><strong>Semester:</strong> {{ $semester->name }}</div>
        <div><strong>Requirement:</strong> {{ $requirement->name }}</div>
        <div><strong>Due Date:</strong> {{ $requirement->due->format('F j, Y') }}</div>
    </div>

    <div class="section-title">FACULTY SUBMISSIONS BY COURSE</div>
    
    @if(count($instructorsWithCourses) > 0)
        <table class="instructor-table">
            <thead>
                <tr class="instructor-header">
                    <th style="width: 40%;">Faculty Information</th>
                    <th style="width: 45%;">Course Details</th>
                    <th style="width: 15%; text-align: center;">Submission</th>
                </tr>
            </thead>
            <tbody>
                @foreach($instructorsWithCourses as $instructorData)
                    @php
                        $instructor = $instructorData['instructor'];
                        $courseSubmissions = $instructorData['courseSubmissions'];
                        $isFirstCourse = true;
                    @endphp
                    
                    @foreach($courseSubmissions as $courseData)
                        @php
                            $course = $courseData['course'];
                            $submission = $courseData['submission'];
                        @endphp
                        
                        <tr class="instructor-row course-row">
                            <td>
                                @if($isFirstCourse)
                                    <strong>{{ $instructor->lastname }}, {{ $instructor->firstname }} {{ $instructor->middlename ? $instructor->middlename . '.' : '' }} {{ $instructor->extensionname ?? '' }}</strong>
                                    <div style="font-size: 10px; color: #666; margin-top: 4px;">
                                        {{ $instructor->college->name ?? 'N/A' }}
                                    </div>
                                    @php $isFirstCourse = false; @endphp
                                @endif
                            </td>
                            <td class="course-info">
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <div style="font-weight: bold; color: var(--primary-green); font-size: 11px;">
                                        {{ $course->course_code ?? 'N/A' }}
                                        <div style="font-weight: normal; font-size: 11px; color: black;">
                                            {{ $course->course_name ?? 'N/A' }}
                                        </div>
                                        <div style="font-weight: normal; font-size: 10px; color: #666;">
                                            {{ $course->program->program_code ?? 'N/A' }} - {{ $course->program->program_name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if($submission)
                                    <div class="status status-submitted">
                                        SUBMITTED
                                    </div>
                                    @if($submission->submitted_at)
                                        <div class="submission-date">
                                            {{ $submission->submitted_at->format('M j, Y') }}
                                        </div>
                                    @endif
                                @else
                                    <div class="status status-no-submission">
                                        NO SUBMISSION
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No instructors with course assignments found for this semester.</div>
    @endif

    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <img src="{{ public_path('images/logo-title.png') }}" alt="iTrack Logo" class="footer-logo">
            </div>
            <div class="footer-right">
                <div class="footer-info">Generated By: iTrack</div>
                <div class="footer-info">Generated On: {{ now()->format('l, F j, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>
</body>
</html>