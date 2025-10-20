<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Faculty Report</title>

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
            /* Calibri Font */
            font-family: 'Calibri', 'Helvetica', sans-serif;
            font-size: 10px;
            line-height: 1.5;
            color: var(--dark-text);
            padding: 40px 30px;
        }

        /* --- Header Styling (Clean and Separated) --- */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 4px solid var(--primary-green);
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            width: 40%;
            text-align: right;
            color: var(--light-text);
        }

        .logo {
            max-width: 150px;
            height: auto;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 18px;
            font-weight: 700;
            color: #000;
            letter-spacing: 0.3px;
        }

        .date-time {
            font-size: 9px;
            margin-top: 5px;
        }

        /* --- Section Titles (Minimalist) --- */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin: 30px 0 10px 0;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 5px;
            page-break-after: avoid;
            letter-spacing: 0.8px;
        }

        /* --- Info Boxes (Inline Display) --- */
        .info-container {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-box {
            display: table-cell;
            width: 50%;
            padding: 10px 0;
            vertical-align: top;
        }

        .info-box:first-child {
            padding-right: 20px;
        }

        .info-header {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 3px;
        }

        .info-details {
            font-size: 10px;
            margin-bottom: 2px;
        }
        
        .info-details strong {
            font-weight: 700;
        }

        /* --- Summary Grid (Inline) --- */
        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 15px;
            border: 1px solid #16A34A;
            overflow: hidden;
            border-collapse: collapse;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 10px 5px;
            text-align: center;
            border-right: 1px solid #16A34A;
            border-radius: 4px;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-name {
            font-size: 9px;
            font-weight: 700;
            color: var(--dark-text);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 3px;
            color: var(--primary-green);
        }

        /* --- Course and Requirement Table (Primary Data View) --- */
        .program-title {
            font-size: 12px;
            font-weight: bold;
            margin: 20px 0 10px 0;
            color: #000;
            padding: 5px 0;
            border-bottom: 2px solid var(--accent-green);
            page-break-after: avoid;
            letter-spacing: 0.2px;
        }
        
        .course-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: auto;
        }

        .course-header-row {
            background-color: var(--light-green);
            font-size: 10px;
            font-weight: bold;
            color: var(--primary-green);
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }
        
        .course-header-row th {
             padding: 8px 10px;
             text-align: left;
        }

        .course-row-details {
            background-color: #f3f4f6;
            font-weight: 700;
            border-top: 1px solid var(--border-color);
        }

        .course-row-details td {
            padding: 6px 10px;
            font-size: 10px;
            color: var(--dark-text);
        }
        
        .req-row td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
            font-size: 9px;
        }
        
        .req-row:last-child td {
            border-bottom: none;
        }

        .req-name {
            font-weight: 700;
            color: var(--dark-text);
        }

        .req-due {
            font-size: 8px;
            color: var(--light-text);
        }

        .file-list {
            margin-top: 5px;
            padding-left: 10px;
            border-left: 2px solid var(--accent-green);
        }

        .file-item {
            font-size: 8px;
            color: #555;
            word-break: break-all;
        }
        
        .no-files {
             font-style: italic;
             color: var(--light-text);
        }

        /* --- Status Styling (Aligned to the right in the table) --- */
        .status {
            text-transform: uppercase;
            font-size: 8px;
            padding: 4px 8px;
            border-radius: 3px;
            display: inline-block;
            font-weight: bold;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        /* New Status Colors - Green Theme */
        .status-under_review { background-color: #dbeafe; color: #1e40af; }
        .status-revision_needed { background-color: #fef9c3; color: #854d09; }
        .status-approved { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        .status-no-submission { background-color: #f3f4f6; color: #1f2937; }

        .submission-date {
            font-size: 7px;
            color: #666;
            margin-top: 4px;
            display: block;
        }

        .no-data {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 20px;
            border: 1px dashed var(--border-color);
            background-color: #fafbfc;
        }

        .course-table table {
            font-family: 'Calibri', 'Helvetica', sans-serif;
        }

        /* Print Specifics */
        @media print {
            .header { border-bottom: 4px solid #000; }
            .program-title { border-bottom: 2px solid #000; }
            
            .course-header-row, .req-row, .summary-item {
                 -webkit-print-color-adjust: exact;
                 color-adjust: exact;
            }
            
            .course-header-row {
                 background-color: #cccccc !important;
                 color: #000 !important;
                 border-top: 1px solid #000;
                 border-bottom: 1px solid #000;
            }
            
            .course-row-details {
                 background-color: #f0f0f0 !important;
            }

            .summary-item {
                 border: 1px solid #000;
            }
            
            .status {
                border: 1px solid #333 !important;
                background-color: #fff !important;
                color: #000 !important;
            }
            
            .file-list {
                border-left: 2px solid #333;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('images/logo-title.png') }}" alt="iTrack Logo" class="logo">
            <div class="report-title">FACULTY END-OF-SEMESTER REPORT</div>
        </div>
        <div class="header-right">
            <div class="date-time">Generated By: {{ config('app.name', 'System') }}</div>
            <div class="date-time">Generated On: {{ now()->format('l, F j, Y \a\t g:i A') }}</div>
        </div>
    </div>

    <div class="section-title">General Information</div>
    <div class="info-container">
        <div class="info-box">
            <div class="info-header">Faculty Details</div>
            <div class="info-details">{{ $user->lastname }}, {{ $user->firstname }} {{ $user->middlename }}. {{ $user->extensionname }}</div>
            <div class="info-details"><strong>Email:</strong> {{ $user->email }}</div>
            <div class="info-details"><strong>College:</strong> {{ $user->college->name ?? 'N/A' }}</div>
        </div>
        
        <div class="info-box">
            <div class="info-header">Semester Details</div>
            <div class="info-details">{{ $semester->name }}</div>
            <div class="info-details"><strong>Start Date:</strong> {{ $semester->start_date->format('F j, Y') }}</div>
            <div class="info-details"><strong>End Date:</strong> {{ $semester->end_date->format('F j, Y') }}</div>
        </div>
    </div>

    <div class="section-title">Overall Submission Summary</div>
    @php
        $totalRequirements = $requirements->count() * $assignedCourses->count();
        $submittedCount = 0;
        $approvedCount = 0;
        $rejectedCount = 0;
        $noSubmissionCount = 0;
        
        foreach($assignedCourses as $assignment) {
            foreach($requirements as $requirement) {
                $key = $assignment->course_id . '_' . $requirement->id;
                $submissions = $groupedSubmissions[$key] ?? [];
                
                if (count($submissions) > 0) {
                    foreach ($submissions as $submission) {
                        $submittedCount++;
                        if (strtolower($submission->status) === 'approved') $approvedCount++;
                        if (strtolower($submission->status) === 'rejected') $rejectedCount++;
                    }
                } else {
                    $noSubmissionCount++;
                }
            }
        }
    @endphp
    
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-name">Total Requirements</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $totalRequirements }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Submitted</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $submittedCount }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Approved</div>
            <div class="summary-value" style="color: var(--primary-green);">{{ $approvedCount }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">No Submission</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $noSubmissionCount }}</div>
        </div>
    </div>

    <div class="section-title">Detailed Requirements Checklist</div>

    @php
        $coursesByProgram = $assignedCourses->groupBy(function($assignment) {
            return $assignment->course->program->id;
        });
    @endphp

    @forelse($coursesByProgram as $programId => $programCourses)
        @php $program = $programCourses->first()->course->program; @endphp
        <div class="program-title">{{ $program->program_code }} - {{ $program->program_name }}</div>
        
        @foreach($programCourses as $assignment)
            <table class="course-table">
                <thead>
                    <tr class="course-row-details">
                        <td colspan="4" style="padding: 6px 10px; font-size: 10px; color: var(--dark-text);">
                            <table style="width: 100%; border-collapse: collapse; font-family: 'Calibri', 'Helvetica', sans-serif;">
                                <tr>
                                    <td style="width: 60%; padding: 0; border: none; font-weight: 700;">
                                        <strong>COURSE:</strong> {{ $assignment->course->course_code }} - {{ $assignment->course->course_name }}
                                    </td>
                                    <td style="width: 40%; padding: 0; border: none; text-align: right; font-weight: 700;">
                                        <strong>TYPE:</strong> {{ $assignment->course->courseType->name ?? 'N/A' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr class="course-header-row">
                        <th style="width: 30%;">Requirement</th>
                        <th style="width: 15%;">Due Date</th>
                        <th style="width: 40%;">Submitted Files</th>
                        <th style="width: 15%; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requirements as $requirement)
                        @php
                            $key = $assignment->course_id . '_' . $requirement->id;
                            $submissionsForThisRequirement = $groupedSubmissions[$key] ?? [];
                            $submissionCount = count($submissionsForThisRequirement);
                        @endphp
                        
                        @if($submissionCount > 0)
                            @foreach($submissionsForThisRequirement as $index => $submission)
                                <tr class="req-row">
                                    <td>
                                        @if($index === 0)
                                            <div class="req-name">{{ $requirement->name }}</div>
                                        @endif
                                    </td>
                                    
                                    <td>
                                        @if($index === 0)
                                            <div class="req-due">{{ $requirement->due->format('M j, Y') }}</div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="file-list">
                                            @if($submission->media->count() > 0)
                                                @foreach($submission->media as $file)
                                                    <div class="file-item">• {{ $file->file_name }}</div>
                                                @endforeach
                                            @else
                                                <div class="no-files">No files in this submission</div>
                                            @endif
                                        </div>
                                    </td>

                                    <td style="text-align: right;">
                                        <div class="status status-{{ strtolower($submission->status) }}">
                                            {{ \App\Models\SubmittedRequirement::statuses()[$submission->status] ?? $submission->status }}
                                        </div>
                                        @if($submission->submitted_at)
                                            <div class="submission-date">
                                                {{ $submission->submitted_at->format('M j, Y') }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="req-row">
                                <td>
                                    <div class="req-name">{{ $requirement->name }}</div>
                                </td>
                                
                                <td>
                                    <div class="req-due">{{ $requirement->due->format('M j, Y') }}</div>
                                </td>

                                <td>
                                    <div class="file-list">
                                        <div class="no-files">No submission</div>
                                    </div>
                                </td>

                                <td style="text-align: right;">
                                    <div class="status status-no-submission">
                                        
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @empty
        <div class="no-data">No assigned courses found for this semester.</div>
    @endforelse
</body>
</html>