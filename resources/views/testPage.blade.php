<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Laravel') }}</title>

        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
                color: #333;
                padding: 20px;
            }

            .header {
                display: table;
                width: 100%;
                margin-bottom: 20px;
            }

            .header-left {
                display: table-cell;
                vertical-align: middle;
                width: 50%;
            }

            .header-right {
                display: table-cell;
                vertical-align: middle;
                width: 50%;
                text-align: right;
            }

            .logo {
                max-width: 160px;
                height: auto;
            }

            .date-time {
                font-size: 10px;
                color: #666;
            }

            .divider {
                border-top: 1px solid #ddd;
                margin: 15px 0;
            }

            .section {
                margin-bottom: 25px;
            }

            .section-title {
                font-size: 14px;
                font-weight: bold;
                text-transform: uppercase;
                margin-bottom: 15px;
                color: #2c3e50;
                border-bottom: 2px solid #3498db;
                padding-bottom: 5px;
            }

            .requirement-item {
                border: 1px solid #e0e0e0;
                border-radius: 4px;
                padding: 12px;
                margin-bottom: 10px;
                background-color: #fafafa;
            }

            .requirement-title {
                font-size: 11px;
                font-weight: bold;
                margin-bottom: 6px;
                color: #2c3e50;
                text-transform: capitalize;
            }

            .requirement-detail {
                font-size: 10px;
                margin-bottom: 3px;
                color: #555;
            }

            .requirement-detail strong {
                color: #333;
            }

            .no-data {
                text-align: center;
                color: #888;
                font-style: italic;
                padding: 20px;
                border: 1px dashed #ccc;
                border-radius: 4px;
                background-color: #f9f9f9;
            }

            .status {
                text-transform: capitalize;
            }

            .status-pending {
                color: #f39c12;
            }

            .status-approved {
                color: #27ae60;
            }

            .status-rejected {
                color: #e74c3c;
            }

            /* Page break helpers for multi-page documents */
            .page-break {
                page-break-before: always;
            }

            .no-break {
                page-break-inside: avoid;
            }

            /* Table layout for better compatibility */
            .flex-table {
                display: table;
                width: 100%;
            }

            .flex-row {
                display: table-row;
            }

            .flex-cell {
                display: table-cell;
                padding: 5px;
                vertical-align: top;
            }

            /* Print-friendly colors */
            @media print {
                body {
                    color: #000;
                }

                .requirement-item {
                    background-color: #fff;
                    border: 1px solid #000;
                }

                .section-title {
                    border-bottom: 2px solid #000;
                }
            }
        </style>
    </head>

    <body>
        <!-- Header Section -->
        <div class="header">
            <div class="header-left">
                <img src="{{ public_path('images/logo-title.png') }}" alt="iTrack Logo" class="logo">
            </div>
            <div class="header-right">
                <div class="date-time">{{ now()->format('l, F j, Y \a\t g:i A') }}</div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="section">
            <div class="requirement-title">{{ $user->lastname }}, {{ $user->firstname }} {{ $user->middlename }}.
                {{ $user->extensionname }}</div>
            <div class="requirement-detail">{{ $user->email }}</div>
            <div class="requirement-detail">{{ $user->college->name }}</div>
            <div class="requirement-detail">{{ $user->department->name }}</div>

            <div class="requirement-title">{{ $semester->name }}</div>
            <div class="requirement-detail"><strong>Starting Date:</strong> {{ $semester->start_date->format('F j, Y') }}</div>
            <div class="requirement-detail"><strong>Ending Date:</strong> {{ $semester->end_date->format('F j, Y') }}</div>

        </div>

        <div class="divider"></div>

        <!-- List of Assigned Requirements -->
        <div class="section">
            <div class="section-title">List of Assigned Requirements</div>

            @forelse ($requirements as $requirement)
                <div class="requirement-item no-break">
                    <div class="requirement-title">
                        {{ $requirement->name }} - {{ $requirement->description }}
                    </div>
                    <div class="requirement-detail">
                        <strong>Due:</strong> {{ $requirement->due->format('F j, Y') }}
                    </div>
                    <div class="requirement-detail">
                        <strong>Created At:</strong> {{ $requirement->created_at->format('F j, Y \a\t g:i A') }}
                    </div>
                </div>
            @empty
                <div class="no-data">No assigned requirements found.</div>
            @endforelse
        </div>

        <div class="divider"></div>

        <!-- List of Submitted Requirements -->
        <div class="section">
            <div class="section-title">List of Submitted Requirements</div>

            @forelse ($submittedRequirements as $submission)
                <div class="requirement-item no-break">
                    <div class="requirement-title">
                        {{ $submission->requirement->name }} - {{ $submission->requirement->description }}
                    </div>
                    <div class="requirement-detail">
                        <strong>Due:</strong> {{ $submission->requirement->due->format('F j, Y') }}
                    </div>
                    <div class="requirement-detail">
                        <strong>Submitted On:</strong> {{ $submission->created_at->format('F j, Y \a\t g:i A') }}
                    </div>
                    <div class="requirement-detail">
                        <strong>Status:</strong>
                        <span
                            class="status {{ $submission->status ? 'status-' . strtolower($submission->status) : '' }}">
                            {{ $submission->status ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="no-data">No submitted requirements found.</div>
            @endforelse
        </div>
    </body>

</html>
