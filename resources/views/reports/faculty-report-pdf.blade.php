<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faculty Report - {{ $user->firstname }} {{ $user->middlename }} {{ $user->lastname }} {{ $user->extensionname }}</title>

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
            font-size: 12px; /* Increased from 10px */
            color: black;
            margin: 0.4in 1in; /* 0.4 inch top/bottom, 1 inch left/right */
        }

        /* --- Updated Header Styling (Letterhead Style) --- */
        .header {
            width: 100%;
            margin-bottom: 25px; /* Increased from 20px */
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
            vertical-align: right;
            width: 25%; 
            text-align: right;
            font-family: 'Arial', sans-serif;
        }

        .header-center {
            display: table-cell;
            vertical-align: middle;
            width: 50%; 
            text-align: center; 
            padding: 0 1px; 
            font-family: 'Arial', sans-serif;
        }

        .logo-right {
            display: table-cell;
            vertical-align: left;
            width: 25%; 
            text-align: left;
            padding-left: 13px; 
            font-family: 'Arial', sans-serif;
        }

        .logo {
            max-height: 100px; 
            padding-top: 5px;
        }

        /* CHANGED: Added text-align: center to university-info to center all text */
        .university-info {
            margin: 0;
            padding: 0;
            line-height: 0.9;
            text-align: center;
            font-family: 'Arial', sans-serif;
        }

        .republic {
            font-size: 12px;
            font-weight: normal;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        .university-name {
            font-size: 17px; 
            font-weight: bold;
            margin: 4px 0 0 0; 
            text-transform: uppercase;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        .campus-name {
            font-size: 12px; 
            font-weight: bold;
            margin: 5px 0 0 0; 
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        .address {
            font-size: 12px; 
            margin: 4px 0 0 0; 
            font-family: 'Arial', sans-serif;
        }

        .contact-info {
            font-size: 12px; 
            margin: 3px 0 0 0; 
            font-family: 'Arial', sans-serif;
        }

        .website {
            font-size: 12px; /* Increased from 9px */
            margin: 3px 0 0 0; /* Increased from 2px */
            font-style: italic;
            font-family: 'Arial', sans-serif;
        }

        .college-name {
            font-size: 16px; /* Same as university-name */
            font-weight: bold;
            margin: 10px 0 0 0; /* Increased from 8px */
            text-transform: uppercase;
            white-space: nowrap;
            font-family: 'Arial', sans-serif;
        }

        .college-divider {
            width: 100%;
            height: 2.5px;
            background-color: black;
            margin: 15px 0 0 0;
            border: none;
        }

        .report-title {
            font-size: 14px; /* Changed from 18px to 14px */
            font-weight: bold;
            margin: 18px 0 0 0; /* Increased from 15px */
            text-transform: uppercase;
            font-family: 'Arial', sans-serif;
        }

        /* Footer Styling */
        .footer {
            width: 100%;
            margin-top: 35px; /* Increased from 30px */
            padding-top: 12px; /* Increased from 10px */
            border-top: 2px solid #1f2937;
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

        .footer-logo {
            max-width: 120px; /* Increased from 100px */
            height: auto;
        }

        .footer-info {
            font-size: 11px; /* Increased from 9px */
            font-family: 'Arial', sans-serif;
        }

        /* --- Section Titles (Minimalist) --- */
        .section-title {
            font-size: 14px; /* Changed from 16px to 14px */
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin: 30px 0 12px 0; /* Increased from 25px/10px */
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 6px; /* Increased from 5px */
            page-break-after: avoid;
            letter-spacing: 0.8px;
            font-family: 'Arial', sans-serif;
        }

        /* --- Info Boxes (Inline Display) --- */
        .info-container {
            display: table;
            width: 100%;
            margin-bottom: 25px; /* Increased from 20px */
            border-collapse: collapse;
            font-family: 'Arial', sans-serif;
        }

        .info-box {
            display: table-cell;
            width: 50%;
            padding: 12px 0; /* Increased from 10px */
            vertical-align: top;
            font-family: 'Arial', sans-serif;
        }

        .info-box:first-child {
            padding-right: 25px; /* Increased from 20px */
        }

        .info-header {
            font-size: 13px; /* Increased from 11px */
            font-weight: bold;
            color: #000;
            margin-bottom: 6px; /* Increased from 5px */
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 4px; /* Increased from 3px */
            font-family: 'Arial', sans-serif;
        }

        .info-details {
            font-size: 12px; /* Increased from 10px */
            margin-bottom: 3px; /* Increased from 2px */
            font-family: 'Arial', sans-serif;
        }
        
        .info-details strong {
            font-weight: 700;
            font-family: 'Arial', sans-serif;
        }

        /* --- Summary Grid (Inline) --- */
        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 18px; /* Increased from 15px */
            border: 1px solid #16A34A;
            overflow: hidden;
            border-collapse: collapse;
            font-family: 'Arial', sans-serif;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 12px 6px; /* Increased from 10px/5px */
            text-align: center;
            border-right: 1px solid #16A34A;
            border-radius: 4px;
            font-family: 'Arial', sans-serif;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-name {
            font-size: 11px; /* Increased from 9px */
            font-weight: 700;
            color: var(--dark-text);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Arial', sans-serif;
        }

        .summary-value {
            font-size: 20px; /* Increased from 18px */
            font-weight: bold;
            margin-top: 4px; /* Increased from 3px */
            color: var(--primary-green);
            font-family: 'Arial', sans-serif;
        }

        /* --- Course and Requirement Table (Primary Data View) --- */
        .program-title {
            font-size: 14px; /* Changed from 14px to 14px (no change but keeping for consistency) */
            font-weight: bold;
            margin: 25px 0 12px 0; /* Increased from 20px/10px */
            color: #000;
            padding: 6px 0; /* Increased from 5px */
            border-bottom: 2px solid var(--accent-green);
            page-break-after: avoid;
            letter-spacing: 0.2px;
            font-family: 'Arial', sans-serif;
        }
        
        .course-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px; /* Increased from 20px */
            page-break-inside: auto;
            font-family: 'Arial', sans-serif;
            table-layout: fixed; /* Added for fixed column widths */
        }

        .course-header-row {
            background-color: var(--light-green);
            font-size: 12px; /* Increased from 10px */
            font-weight: bold;
            color: var(--primary-green);
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            font-family: 'Arial', sans-serif;
        }
        
        .course-header-row th {
             padding: 10px 12px; /* Increased from 8px/10px */
             text-align: left;
             font-family: 'Arial', sans-serif;
        }

        .course-row-details {
            background-color: #f3f4f6;
            font-weight: 700;
            border-top: 1px solid var(--border-color);
            font-family: 'Arial', sans-serif;
        }

        .course-row-details td {
            padding: 8px 12px; /* Increased from 6px/10px */
            font-size: 12px; /* Increased from 10px */
            color: var(--dark-text);
            font-family: 'Arial', sans-serif;
        }
        
        .req-row td {
            padding: 10px 12px; /* Increased from 8px/10px */
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
            font-size: 11px; /* Increased from 9px */
            font-family: 'Arial', sans-serif;
        }
        
        .req-row:last-child td {
            border-bottom: none;
        }

        .req-name {
            font-weight: 700;
            color: var(--dark-text);
            font-family: 'Arial', sans-serif;
        }

        .req-due {
            font-size: 10px; /* Increased from 8px */
            color: var(--light-text);
            font-family: 'Arial', sans-serif;
        }

        .file-list {
            margin-top: 6px; /* Increased from 5px */
            padding-left: 12px; /* Increased from 10px */
            border-left: 2px solid var(--accent-green);
            font-family: 'Arial', sans-serif;
        }

        .file-item {
            font-size: 10px; /* Increased from 8px */
            color: #555;
            word-break: break-word; /* Changed from break-all to break-word */
            overflow-wrap: break-word; /* Added for better text wrapping */
            font-family: 'Arial', sans-serif;
        }
        
        .no-files {
             font-style: italic;
             color: var(--light-text);
             font-family: 'Arial', sans-serif;
        }

        /* --- Status Styling (Aligned to the right in the table) --- */
        .status {
            /* Removed text-transform: uppercase */
            font-size: 10px; /* Increased from 8px */
            padding: 5px 9px; /* Increased from 4px/8px */
            border-radius: 9999px; /* Changed from 3px to 9999px for rounded-full effect */
            display: inline-block;
            font-weight: normal; /* Changed from bold to normal */
            letter-spacing: 0.5px;
            white-space: nowrap;
            font-family: 'Arial', sans-serif;
        }

        /* New Status Colors - Green Theme */
        .status-under_review { background-color: #dbeafe; color: #1e40af; }
        .status-revision_needed { background-color: #fef9c3; color: #854d09; }
        .status-approved { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        .status-no-submission { background-color: #f3f4f6; color: #1f2937; }

        .submission-date {
            font-size: 9px; /* Increased from 7px */
            color: #666;
            margin-top: 5px; /* Increased from 4px */
            display: block;
            font-family: 'Arial', sans-serif;
        }

        .no-data {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 25px; /* Increased from 20px */
            border: 1px dashed var(--border-color);
            background-color: #fafbfc;
            font-family: 'Arial', sans-serif;
        }

        .course-table table {
            font-family: 'Arial', sans-serif;
        }

        /* Filter Applied Styling - COPIED FROM REQUIREMENT REPORT */
        .filter-applied {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            font-family: 'Arial', sans-serif;
        }

        /* Prepared by section - UPDATED FOR PAGE BREAK FIX */
        .prepared-by-section {
            margin-top: 40px;
            page-break-inside: avoid;
            page-break-before: auto;
        }

        .prepared-by {
            text-align: right;
            font-family: 'Arial', sans-serif;
            padding: 20px 0;
        }

        .prepared-by-label {
            margin-bottom: 30px;
            font-family: 'Arial', sans-serif;
        }

        .prepared-by-name {
            font-weight: bold;
            margin-bottom: 2px;
            font-family: 'Arial', sans-serif;
        }

        .prepared-by-position {
            font-family: 'Arial', sans-serif;
        }

        /* Print Specifics - UPDATED FOR PREPARED BY SECTION */
        @media print {
            body {
                margin: 0.5in 1in;
                font-family: 'Arial', sans-serif;
            }
            
            .header { border-bottom: none; font-family: 'Arial', sans-serif; }
            .footer { border-top: 2px solid #000; font-family: 'Arial', sans-serif; }
            .program-title { border-bottom: 2px solid #000; font-family: 'Arial', sans-serif; }
            
            .course-header-row, .req-row, .summary-item {
                 -webkit-print-color-adjust: exact;
                 color-adjust: exact;
                 font-family: 'Arial', sans-serif;
            }
            
            .course-header-row {
                 background-color: #cccccc !important;
                 color: #000 !important;
                 border-top: 1px solid #000;
                 border-bottom: 1px solid #000;
                 font-family: 'Arial', sans-serif;
            }
            
            .course-row-details {
                 background-color: #f0f0f0 !important;
                 font-family: 'Arial', sans-serif;
            }

            .summary-item {
                 border: 1px solid #000;
                 font-family: 'Arial', sans-serif;
            }
            
            .status {
                border: 1px solid #333 !important;
                background-color: #fff !important;
                color: #000 !important;
                font-family: 'Arial', sans-serif;
                border-radius: 9999px; /* Added for print */
            }
            
            .file-list {
                border-left: 2px solid #333;
                font-family: 'Arial', sans-serif;
            }

            .filter-applied {
                background-color: #f0f0f0 !important;
                border-left: 4px solid #000 !important;
                font-family: 'Arial', sans-serif;
            }

            /* Enhanced page break control for prepared by section */
            .prepared-by-section {
                page-break-inside: avoid !important;
                page-break-before: avoid !important;
                margin-top: 30px;
            }

            /* Force new page if not enough space */
            .prepared-by-new-page {
                page-break-before: always !important;
            }
        }
    </style>
</head>

<body>
    @php
        $formattedUserMiddleName = $user->middlename ? substr(trim($user->middlename), 0, 1) . '.' : '';
        $formattedPreparedByMiddleName = Auth::user()->middlename ? substr(trim(Auth::user()->middlename), 0, 1) . '.' : '';
    @endphp

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
        
        <div class="report-title">FACULTY END-OF-SEMESTER REPORT</div>
    </div>

    

    <div class="info-container">
        <div class="info-box">
            <div class="info-header">Faculty Details</div>
            <div class="info-details"><strong>Name: </strong>{{ $user->firstname }} {{ $formattedUserMiddleName }} {{ $user->lastname }} {{ $user->extensionname }}</div>
            <div class="info-details"><strong>Position:</strong> {{ $user->position ?? 'N/A' }}</div>
            <div class="info-details"><strong>Email:</strong> {{ $user->email }}</div>
            <div class="info-details"><strong>College:</strong> {{ $user->college->name ?? 'N/A' }}</div>
        </div>
        
        <div class="info-box">
            <div class="info-header">Semester Details</div>
            <div class="info-details"><strong>Semester: </strong>{{ $semester->name }}</div>
            <div class="info-details"><strong>Start Date:</strong> {{ $semester->start_date->format('F j, Y') }}</div>
            <div class="info-details"><strong>End Date:</strong> {{ $semester->end_date->format('F j, Y') }}</div>
        </div>
    </div>

    @if(isset($submissionFilter) && $submissionFilter !== 'all')
    <div class="filter-applied">
        <strong>Filter Applied:</strong> 
        @if($submissionFilter === 'with_submission')
            Showing Only Requirements With Submission
        @elseif($submissionFilter === 'no_submission')
            Showing Only Requirements With No Submission
        @endif
    </div>
    @endif

    <div class="section-title">SUBMISSION SUMMARY</div>
    
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-name">Total Requirements</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $summaryData['total_requirements'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Submitted</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $summaryData['submitted_count'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Approved</div>
            <div class="summary-value" style="color: var(--primary-green);">{{ $summaryData['approved_count'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">No Submission</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $summaryData['no_submission_count'] }}</div>
        </div>
    </div>

    <div class="section-title">DETAILED REQUIREMENTS CHECKLIST</div>

    @php
        $detailedData = $detailedRequirements;
    @endphp

    @forelse($detailedData['courses_by_program'] as $programId => $programCourses)
        @php $program = $programCourses->first()->course->program; @endphp
        <div class="program-title">{{ $program->program_code }} - {{ $program->program_name }}</div>
        
        @foreach($programCourses as $assignment)
            <table class="course-table">
                <thead>
                    <tr class="course-row-details">
                        <td colspan="4" style="padding: 8px 12px; font-size: 12px; color: var(--dark-text); font-family: 'Arial', sans-serif;">
                            <table style="width: 100%; border-collapse: collapse; font-family: 'Arial', sans-serif;">
                                <tr>
                                    <td style="width: 60%; padding: 0; border: none; font-weight: 700; font-family: 'Arial', sans-serif;">
                                         {{ $assignment->course->course_code }} - {{ $assignment->course->course_name }}
                                    </td>
                                    <td style="width: 40%; padding: 0; border: none; text-align: right; font-weight: 700; font-family: 'Arial', sans-serif;">
                                         {{ $assignment->course->courseType->name ?? 'N/A' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr class="course-header-row">
                        <th style="width: 35%; font-family: 'Arial', sans-serif;">Requirement</th>
                        <th style="width: 10%; font-family: 'Arial', sans-serif;">Due Date</th>
                        <th style="width: 45%; font-family: 'Arial', sans-serif;">Submitted Files</th>
                        <th style="width: 10%; text-align: center; font-family: 'Arial', sans-serif;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignment->filtered_requirements as $requirement)
                        @php
                            $key = $assignment->course_id . '_' . $requirement->id;
                            $submissions = $detailedData['grouped_submissions'][$key] ?? [];
                            $submissionCount = count($submissions);
                        @endphp
                        
                        @if($submissionCount > 0)
                            @foreach($submissions as $index => $submission)
                                <tr class="req-row">
                                    <td style="width: 35%;">
                                        @if($index === 0)
                                            <div class="req-name">{{ $requirement->name }}</div>
                                        @endif
                                    </td>
                                    
                                    <td style="width: 10%;">
                                        @if($index === 0)
                                            <div class="req-due">{{ $requirement->due->format('M j, Y') }}</div>
                                        @endif
                                    </td>

                                    <td style="width: 45%;">
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

                                    <td style="width: 10%; text-align: right;">
                                        @php
                                            $statusClass = match(strtolower($submission->status)) {
                                                'under_review' => 'status-under_review',
                                                'revision_needed' => 'status-revision_needed',
                                                'approved' => 'status-approved',
                                                'rejected' => 'status-rejected',
                                                default => 'status-no-submission'
                                            };
                                        @endphp
                                        <div class="status {{ $statusClass }}">
                                            {{ \App\Models\SubmittedRequirement::statuses()[$submission->status] ?? ucfirst(str_replace('_', ' ', $submission->status)) }}
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
                                <td style="width: 35%;">
                                    <div class="req-name">{{ $requirement->name }}</div>
                                </td>
                                    
                                <td style="width: 10%;">
                                    <div class="req-due">{{ $requirement->due->format('M j, Y') }}</div>
                                </td>

                                <td style="width: 45%;">
                                    <div class="file-list">
                                        <div class="no-files">No submission</div>
                                    </div>
                                </td>

                                <td style="width: 10%; text-align: right;">
                                    {{-- Status column left blank for no submission --}}
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

    <!-- Prepared by Section with enhanced page break control -->
    <div class="prepared-by-section">
        <div class="prepared-by">
            <div class="prepared-by-label">Prepared by:</div>
            <div class="prepared-by-name">{{ Auth::user()->firstname }} {{ $formattedPreparedByMiddleName }} {{ Auth::user()->lastname }} {{ Auth::user()->extensionname }}</div>
            <div class="prepared-by-position">{{ Auth::user()->position ?? 'Administrator' }}</div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                <img src="{{ public_path('images/logo-title.png') }}" alt="iTrack Logo" class="footer-logo">
            </div>
            <div class="footer-right">
                <div class="footer-info">Generated On: {{ now()->format('l, F j, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>
</body>
</html>