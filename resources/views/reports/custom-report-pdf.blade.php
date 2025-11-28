<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Custom Year Report - {{ $summaryStats['date_range']['formatted'] }}</title>

    <style>
        :root {
            /* Kept for color codes used in statuses, but main border color is black */
            --primary-green: #01a73e;
            --dark-green: #006b2f;
            --light-green: #e8f5e9;
            --accent-green: #00c853;
            --dark-text: #1f2937;
            --light-text: #374151;
            --border-color: #d1d5db; /* Retaining this but using #000 for critical borders */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px; /* Reduced base font size */
            color: black;
            margin: 0.4in 1in;
            line-height: 1.2;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
            text-align: center;
            font-family: 'Arial', sans-serif;
        }

        .header-content {
            display: table;
            width: 100%;
            margin: 0;
            padding: 0;
            table-layout: fixed;
        }

        .logo-left, .logo-right {
            display: table-cell;
            vertical-align: middle;
            width: 25%;
        }

        .logo-left { text-align: right; }
        .logo-right { text-align: left; padding-left: 13px; }

        .header-center {
            display: table-cell;
            vertical-align: middle;
            width: 50%;
            text-align: center;
            padding: 0 1px;
        }

        .logo {
            max-height: 80px; /* Reduced logo size */
            padding-top: 5px;
        }

        .university-info {
            margin: 0;
            padding: 0;
            line-height: 0.9;
            text-align: center;
        }

        .republic { font-size: 11px; font-weight: normal; margin: 0; padding: 0; }
        .university-name { font-size: 15px; font-weight: bold; margin: 4px 0 0 0; text-transform: uppercase; }
        .campus-name { font-size: 11px; font-weight: bold; margin: 5px 0 0 0; }
        .address { font-size: 11px; margin: 4px 0 0 0; }
        .contact-info { font-size: 11px; margin: 3px 0 0 0; }
        .website { font-size: 11px; margin: 3px 0 0 0; font-style: italic; }
        .college-name { font-size: 14px; font-weight: bold; margin: 8px 0 0 0; text-transform: uppercase; }

        .college-divider {
            width: 100%;
            height: 2px;
            background-color: black;
            margin: 12px 0 0 0;
            border: none;
        }

        .report-title {
            font-size: 13px;
            font-weight: bold;
            margin: 15px 0 0 0;
            text-transform: uppercase;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin: 25px 0 10px 0;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 5px;
            page-break-after: avoid;
        }

        /* --- Updated Summary Grid Styles for Thin Black Border --- */
        .summary-grid {
            display: table;
            width: 100%;
            margin: 15px 0;
            border: 1px solid #000; /* Thin black border */
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        
        .summary-item {
            display: table-cell;
            width: 16.66%;
            padding: 8px 4px;
            text-align: center;
            border-right: 1px solid #000; /* Thin black border */
        }

        .summary-item:last-child { border-right: none; }
        /* ---------------------------------------------------- */

        .summary-name {
            font-size: 9px;
            font-weight: 700;
            color: var(--dark-text);
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 3px;
            color: var(--primary-green);
        }

        .faculty-section {
            margin-bottom: 20px;
            /* **OPTIMIZATION:** Removed page-break-inside: avoid; to allow courses to span pages. */
        }

        .faculty-header {
            padding: 8px 10px;
            border: 1px solid #000; /* Changed to black border */
            border-radius: 3px;
            margin-bottom: 10px;
            page-break-after: avoid; /* Ensures the header is not separated from the first course */
        }

        .faculty-header-content {
            display: table;
            width: 100%;
        }

        .faculty-header-left, .faculty-header-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }

        .faculty-name {
            font-size: 12px;
            font-weight: bold;
            color: var(--dark-text);
        }

        .faculty-details {
            font-size: 9px;
            color: var(--light-text);
            margin-top: 3px;
        }

        /* Compact table design */
        .course-wrapper {
            /* ESSENTIAL: Force the entire course assignment table (header + all rows) to stay together */
            page-break-inside: avoid; 
        }

        .course-space {
            margin-top: 8px; /* Standardized space between course tables */
        }

        .course-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0; 
            page-break-inside: auto; 
            font-size: 9px;
            border: 1px solid #000; /* Added black border to table */
        }

        .course-header-row {
            background-color: var(--light-green);
            font-size: 9px;
            font-weight: bold;
            color: var(--primary-green);
            border: 1px solid #000; /* Changed to black border */
        }
        
        .course-header-row th {
            padding: 6px 8px;
            text-align: left;
            border-right: 1px solid #000; /* Changed to black border */
        }

        .course-header-row th:last-child {
            border-right: none;
        }

        .req-row td {
            padding: 6px 8px;
            /* Border style for internal table cells - changed to black */
            border-bottom: 1px solid #000; 
            border-right: 1px solid #000;
            vertical-align: top;
        }

        .req-row td:last-child {
            border-right: none;
        }

        /* The last row of the course table needs the bottom border adjusted if necessary
           But since we're using a wrapper, we'll leave the borders inside. */

        .req-name { 
            font-weight: 700; 
            color: var(--dark-text);
            font-size: 9px;
        }
        .req-due { 
            font-size: 8px; 
            color: var(--light-text);
            margin-top: 1px;
        }

        .status {
            font-size: 8px;
            padding: 3px 6px;
            border-radius: 9999px;
            display: inline-block;
            font-weight: normal;
            white-space: nowrap;
        }

        .status-under_review { background-color: #dbeafe; color: #1e40af; }
        .status-revision_needed { background-color: #fef9c3; color: #854d09; }
        .status-approved { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #fee2e2; color: #991b1b; }
        .status-no-submission { background-color: #f3f4f6; color: #1f2937; }

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

        .footer {
            width: 100%;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 2px solid #1f2937;
            page-break-before: avoid;
        }

        .footer-content { display: table; width: 100%; }
        .footer-left, .footer-right { display: table-cell; vertical-align: middle; width: 50%; }
        .footer-left { text-align: left; }
        .footer-right { text-align: right; color: var(--light-text); }
        .footer-info { font-size: 9px; }

        /* Page break utilities */
        .page-break-before { page-break-before: always; }
        .page-break-after { page-break-after: always; }
        .keep-together { page-break-inside: avoid; }
        .break-inside-avoid { page-break-inside: avoid; }

        /* Compact column widths */
        .col-course { width: 35%; }
        .col-requirement { width: 30%; }
        .col-due { width: 15%; }
        .col-status { width: 20%; text-align: center; }

        @media print {
            body { 
                margin: 0.4in 0.8in; /* Reduced margins for more space */
                font-size: 9px;
            }
            .faculty-section { 
                /* Removed 'page-break-inside: avoid;' from print media too */
                margin-bottom: 15px;
            }
            .course-wrapper {
                 page-break-inside: avoid; /* Essential to keep a single course together */
            }
            .summary-item { border: 1px solid #000; } /* Ensured black border in print */
            
            /* Force page breaks if content is too long */
            .force-page-break {
                page-break-before: always;
            }

            /* Enhanced page break control for approval sections */
            .approval-sections-right {
                page-break-inside: avoid !important;
                page-break-before: avoid !important;
                margin-top: 30px;
                text-align: right;
            }
        }

        /* Alternative layout for very tight spaces */
        .compact-view .course-header-row th,
        .compact-view .req-row td {
            padding: 4px 6px;
        }

        .compact-view .status {
            padding: 2px 4px;
            font-size: 7px;
        }
    </style>
</head>

<body>
    @php
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
        
        <div class="report-title">CUSTOM YEAR FACULTY REPORT</div>
    </div>

    <div class="info-container keep-together" style="margin-bottom: 20px;">
        <div style="text-align: center; padding: 10px; background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 3px; font-size: 10px;">
            <strong>Report Period:</strong> {{ $summaryStats['date_range']['formatted'] }}
            @if($collegeFilter)
                <br><strong>College:</strong> {{ $collegeFilter->name }}
            @endif
            @if($searchFilter)
                <br><strong>Search:</strong> "{{ $searchFilter }}"
            @endif
        </div>
    </div>

    <div class="section-title keep-together">REPORT SUMMARY</div>
    
    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-name">Total Faculty</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $summaryStats['total_faculty'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Total Courses</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $summaryStats['total_courses'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Total Submissions</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $summaryStats['total_submissions'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Approved</div>
            <div class="summary-value" style="color: var(--primary-green);">{{ $summaryStats['total_approved'] }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-name">Submission Rate</div>
            <div class="summary-value" style="color: var(--accent-green);">{{ $summaryStats['overall_submission_rate'] }}%</div>
        </div>
    </div>

    <div class="section-title keep-together">FACULTY DETAILS</div>

    @php
        $facultyCount = 0;
        $maxFacultyPerPage = 3; // Adjust based on your content density
    @endphp

    @forelse($reportData as $index => $facultyData)
        @php $faculty = $facultyData['faculty']; @endphp
        
        {{-- This logic breaks based on an arbitrary count, use page-break-before instead of facultyCount --}}
        @if($index > 0 && $facultyCount >= $maxFacultyPerPage)
            @php $facultyCount = 0; @endphp
            <div class="page-break-before"></div>
        @endif

        {{-- Removed 'break-inside-avoid' here for better space utilization --}}
        <div class="faculty-section"> 
            <div class="faculty-header">
                <div class="faculty-header-content">
                    <div class="faculty-header-left">
                        <div class="faculty-name">{{ $faculty->firstname }} {{ $faculty->middlename ? substr(trim($faculty->middlename), 0, 1) . '.' : '' }} {{ $faculty->lastname }} {{ $faculty->extensionname }}</div>
                        <div class="faculty-details">
                            @if($faculty->teaching_started_at)
                                <div style="margin-bottom: 2px;">
                                    <strong>Teaching Period:</strong> 
                                    {{ \Carbon\Carbon::parse($faculty->teaching_started_at)->format('F j, Y') }} - 
                                    @if($faculty->teaching_ended_at)
                                        {{ \Carbon\Carbon::parse($faculty->teaching_ended_at)->format('F j, Y') }}
                                    @else
                                        <strong>Present</strong>
                                    @endif
                                </div>
                            @endif
                            {{ $facultyData['total_submissions'] }} Submissions ({{ $facultyData['submission_rate'] }}% Rate)
                        </div>
                    </div>
                    <div class="faculty-header-right">
                        <div class="faculty-details">
                            @if($faculty->position)
                                <div style="margin-bottom: 2px;">
                                    <strong>Position:</strong> {{ $faculty->position }}
                                </div>
                            @endif
                            <div style="margin-bottom: 2px;">
                                <strong>Email:</strong> {{ $faculty->email }}
                            </div>
                            <div>
                                <strong>College:</strong> {{ $faculty->college->name ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($facultyData['courses'] as $courseIndex => $courseData)
                @php $assignment = $courseData['assignment']; @endphp
                
                @if($courseIndex > 0)
                    <div class="course-space"></div> @endif
                
                {{-- COURSE WRAPPER: Force the entire course assignment table to stay together on one page --}}
                <div class="course-wrapper break-inside-avoid">
                    <table class="course-table compact-view">
                        <thead>
                            <tr class="course-header-row">
                                <th class="col-course">Course & Semester</th>
                                <th class="col-requirement">Requirement</th>
                                <th class="col-due">Due Date</th>
                                <th class="col-status">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courseData['requirements'] as $reqIndex => $reqData)
                                @php 
                                    $requirement = $reqData['requirement'];
                                    $hasSubmission = $reqData['submission_count'] > 0;
                                    $submission = $hasSubmission ? $reqData['submissions']->first() : null;
                                @endphp
                                <tr class="req-row">
                                    <td class="col-course">
                                        {{-- This logic ensures the course details only appear once per table --}}
                                        @if($reqIndex === 0) 
                                            <div style="font-weight: 700; font-size: 9px;">{{ $assignment->course->course_code }}</div>
                                            <div style="font-size: 8px; color: #666; margin-top: 1px;">
                                                {{ $assignment->course->course_name }}
                                            </div>
                                            <div style="font-size: 8px; color: #666;">
                                                {{ $assignment->course->program->program_name }}
                                            </div>
                                            <div style="font-size: 8px; color: #666;">
                                                {{ $assignment->semester->name }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="col-requirement">
                                        <div class="req-name">{{ $requirement->name }}</div>
                                    </td>
                                    <td class="col-due">
                                        <div class="req-due">{{ $requirement->due->format('M j, Y') }}</div>
                                    </td>
                                    <td class="col-status">
                                        @if($hasSubmission && $submission)
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
                                                <div style="font-size: 7px; color: #666; margin-top: 2px;">
                                                    {{ $submission->submitted_at->format('M j, Y') }}
                                                </div>
                                            @endif
                                        @else
                                            <div class="status status-no-submission">No Submission</div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- END COURSE WRAPPER --}}
            @endforeach
        </div>
        
        @php $facultyCount++; @endphp
    @empty
        <div style="text-align: center; color: #888; font-style: italic; padding: 20px; border: 1px dashed var(--border-color); background-color: #fafbfc; font-size: 10px;">
            No data found for the selected filters.
        </div>
    @endforelse

    <div class="footer">
        <div class="footer-content">
            <div class="footer-right">
                <div class="footer-info">Generated On: {{ now()->format('F j, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Approval Sections aligned to the right -->
    <div class="approval-sections-right keep-together">
        <div class="approval-item">
            <div class="approval-label">Prepared by:</div>
            <div class="approval-spacing"></div>
            <div class="approval-name">{{ Auth::user()->firstname }} {{ $formattedPreparedByMiddleName }} {{ Auth::user()->lastname }} {{ Auth::user()->extensionname }}</div>
            <div class="approval-position">{{ Auth::user()->position ?? 'Administrator' }}</div>
        </div>
        
        <div class="approval-item">
            <div class="approval-label">Noted by:</div>
            <div class="approval-spacing"></div>
            <div class="approval-name">DR. MARIA S. SANTOS</div>
            <div class="approval-position">Dean, Graduate School</div>
        </div>
        
        <div class="approval-item">
            <div class="approval-label">Checked by:</div>
            <div class="approval-spacing"></div>
            <div class="approval-name">DR. JUAN R. CRUZ</div>
            <div class="approval-position">Quality Assurance Director</div>
        </div>
    </div>
</body>
</html>