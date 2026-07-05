<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Report Card - {{ $student->name }}</title>
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        /* ===== BASE RESET ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 15mm 12mm 15mm 12mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1a1a1a;
            font-size: 12px;
            line-height: 1.4;
            background: #fff;
        }

        /* ===== HEADER / SCHOOL INFO ===== */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .logo-cell {
            width: 70px;
            text-align: center;
        }

        .logo-cell img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .logo-placeholder {
            width: 60px;
            height: 60px;
            border: 2px solid #c0392b;
            border-radius: 50%;
            text-align: center;
            line-height: 56px;
            font-size: 22px;
            font-weight: bold;
            color: #c0392b;
            margin: 0 auto;
        }

        .school-info-cell {
            text-align: center;
            padding: 0 10px;
        }

        .school-name {
            font-size: 22px;
            font-weight: bold;
            color: #c0392b;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 2px;
        }

        .school-contact {
            font-size: 10px;
            color: #555;
            margin-bottom: 2px;
        }

        .report-title {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            color: #2c3e50;
            letter-spacing: 1px;
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #ccc;
        }

        .term-year {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }

        /* ===== DIVIDER ===== */
        .divider {
            border: none;
            border-top: 3px solid #c0392b;
            margin: 10px 0;
        }

        .divider-thin {
            border: none;
            border-top: 1px solid #ddd;
            margin: 8px 0;
        }

        /* ===== STUDENT BIO-DATA ===== */
        .bio-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .bio-table td {
            padding: 4px 6px;
            font-size: 12px;
            vertical-align: top;
        }

        .bio-label {
            font-weight: bold;
            color: #444;
            white-space: nowrap;
            width: 130px;
        }

        .bio-value {
            color: #1a1a1a;
            border-bottom: 1px dotted #bbb;
            font-weight: 600;
        }

        /* ===== SECTION HEADINGS ===== */
        .section-heading {
            background-color: #2c3e50;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 5px 10px;
            margin-bottom: 0;
        }

        /* ===== GRADES TABLE ===== */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .grades-table th {
            background-color: #f0f0f0;
            border: 1px solid #bbb;
            padding: 6px 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            color: #333;
        }

        .grades-table td {
            border: 1px solid #ccc;
            padding: 5px 8px;
            text-align: center;
            font-size: 11px;
        }

        .grades-table .subject-cell {
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
        }

        .grades-table .total-cell {
            font-weight: bold;
            color: #c0392b;
        }

        .grades-table .grade-cell {
            font-weight: bold;
            font-size: 12px;
        }

        .grades-table .remarks-cell {
            font-size: 10px;
            text-align: left;
            color: #555;
        }

        .grades-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        /* Grade-specific colors */
        .grade-a {
            color: #27ae60;
        }

        .grade-b {
            color: #2980b9;
        }

        .grade-c {
            color: #8e44ad;
        }

        .grade-d {
            color: #e67e22;
        }

        .grade-e {
            color: #d35400;
        }

        .grade-f {
            color: #c0392b;
        }

        /* ===== SUMMARY ROW ===== */
        .summary-row td {
            background-color: #f7f7f7;
            font-weight: bold;
            border-top: 2px solid #999;
            font-size: 12px;
        }

        /* ===== ATTENDANCE & SUMMARY ===== */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .summary-table td {
            padding: 5px 8px;
            font-size: 11px;
            border: 1px solid #ccc;
        }

        .summary-table .sum-label {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #444;
            width: 40%;
        }

        .summary-table .sum-value {
            text-align: center;
            font-weight: 600;
        }

        /* ===== REMARKS ===== */
        .remarks-section {
            margin-bottom: 14px;
        }

        .remarks-label {
            font-size: 11px;
            font-weight: bold;
            color: #444;
            margin-bottom: 3px;
        }

        .remarks-box {
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 8px 10px;
            min-height: 35px;
            font-size: 11px;
            color: #333;
            font-style: italic;
            background-color: #fdfdfd;
        }

        /* ===== GRADING KEY ===== */
        .grading-key {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 10px;
        }

        .grading-key th {
            background-color: #2c3e50;
            color: #fff;
            padding: 4px 6px;
            text-align: center;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        .grading-key td {
            border: 1px solid #ccc;
            padding: 4px 6px;
            text-align: center;
        }

        .grading-key .range-row td {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .grading-key .desc-row td {
            font-size: 9px;
            color: #555;
        }

        /* ===== SIGNATURE LINE ===== */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        .signatures-table td {
            width: 33%;
            text-align: center;
            padding-top: 4px;
            font-size: 10px;
            color: #555;
            vertical-align: top;
        }

        .sig-line {
            border-top: 1px solid #333;
            display: inline-block;
            width: 80%;
            margin-bottom: 3px;
        }

        .sig-date {
            font-size: 9px;
            color: #999;
            margin-top: 2px;
        }

        /* ===== FOOTER ===== */
        .footer {
            text-align: center;
            font-size: 9px;
            color: #999;
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #eee;
        }

        /* ===== ORDINAL HELPER ===== */
        sup {
            font-size: 8px;
        }
    </style>
</head>

<body>

    {{-- ========== SCHOOL HEADER ========== --}}
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if($school->logo)
                    <img src="{{ public_path('storage/' . $school->logo) }}" alt="School Logo">
                @else
                    <div class="logo-placeholder">
                        {{ strtoupper(substr($school->name ?? 'S', 0, 1)) }}
                    </div>
                @endif
            </td>
            <td class="school-info-cell">
                <div class="school-name">{{ $school->name ?? 'School Name' }}</div>
                @if($school->contact_details)
                    <div class="school-contact">{{ $school->contact_details }}</div>
                @endif
                <div class="report-title">Terminal Report Card</div>
                <div class="term-year">
                    {{ $term->academicYear->year_name ?? 'Academic Year' }} &mdash; {{ $term->term_name ?? 'Term' }}
                </div>
            </td>
            <td class="logo-cell">
                {{-- Symmetry placeholder for right side --}}
                @if($school->logo)
                    <img src="{{ public_path('storage/' . $school->logo) }}" alt="School Logo">
                @else
                    <div class="logo-placeholder">
                        {{ strtoupper(substr($school->name ?? 'S', 0, 1)) }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- ========== STUDENT BIO-DATA ========== --}}
    @php
        $position = $reportCard->position_in_class;
        if ($position) {
            $suffix = 'th';
            if (!in_array($position % 100, [11, 12, 13])) {
                switch ($position % 10) {
                    case 1:
                        $suffix = 'st';
                        break;
                    case 2:
                        $suffix = 'nd';
                        break;
                    case 3:
                        $suffix = 'rd';
                        break;
                }
            }
            $positionDisplay = $position . $suffix;
        } else {
            $positionDisplay = 'N/A';
        }

        $totalStudents = $reportCard->schoolClass->students->count();
    @endphp

    <table class="bio-table">
        <tr>
            <td class="bio-label">Name of Pupil:</td>
            <td class="bio-value" colspan="3">{{ strtoupper($student->name) }}</td>
        </tr>
        <tr>
            <td class="bio-label">Class:</td>
            <td class="bio-value">{{ $reportCard->schoolClass->name }}</td>
            <td class="bio-label" style="text-align: right;">No. on Roll:</td>
            <td class="bio-value" style="text-align: center;">{{ $totalStudents }}</td>
        </tr>
        <tr>
            <td class="bio-label">Position in Class:</td>
            <td class="bio-value">
                <strong style="font-size: 14px; color: #c0392b;">{{ $positionDisplay }}</strong>
                out of {{ $totalStudents }}
            </td>
            <td class="bio-label" style="text-align: right;">Average Score:</td>
            <td class="bio-value" style="text-align: center;">
                <strong style="font-size: 14px;">{{ number_format($reportCard->average_score, 1) }}%</strong>
            </td>
        </tr>
        @if($reportCard->attendance_count !== null || $reportCard->total_attendance !== null)
            <tr>
                <td class="bio-label">Attendance:</td>
                <td class="bio-value" colspan="3">
                    {{ $reportCard->attendance_count ?? '—' }} out of {{ $reportCard->total_attendance ?? '—' }} days
                </td>
            </tr>
        @endif
    </table>

    <hr class="divider-thin">

    {{-- ========== ACADEMIC PERFORMANCE TABLE ========== --}}
    <div class="section-heading">Academic Performance</div>

    <table class="grades-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 25%; text-align: left;">Subject</th>
                <th style="width: 12%;">Class Score</th>
                <th style="width: 12%;">Exam Score</th>
                <th style="width: 12%;">Total (100%)</th>
                <th style="width: 8%;">Grade</th>
                <th style="width: 26%; text-align: left;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportCard->details->sortBy(function ($detail) {
                return $detail->subject->name; }) as $index => $detail)
                @php
                    $gradeClass = 'grade-' . strtolower($detail->grade ?? 'f');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="subject-cell">{{ $detail->subject->name }}</td>
                    <td>{{ number_format($detail->class_score, 1) }}</td>
                    <td>{{ number_format($detail->exam_score, 1) }}</td>
                    <td class="total-cell">{{ number_format($detail->total_score, 1) }}</td>
                    <td class="grade-cell {{ $gradeClass }}">{{ $detail->grade }}</td>
                    <td class="remarks-cell">{{ $detail->remarks }}</td>
                </tr>
            @endforeach

            {{-- Summary row --}}
            <tr class="summary-row">
                <td colspan="2" style="text-align: right;">OVERALL</td>
                <td>&mdash;</td>
                <td>&mdash;</td>
                <td class="total-cell" style="font-size: 13px;">{{ number_format($reportCard->total_score, 1) }}</td>
                <td colspan="2" style="text-align: left; font-size: 11px;">
                    Aggregate Average: <strong>{{ number_format($reportCard->average_score, 1) }}%</strong>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- ========== REMARKS ========== --}}
    <div class="remarks-section">
        <div class="remarks-label">Class Teacher's Remarks:</div>
        <div class="remarks-box">
            {{ $reportCard->teacher_remarks ?? 'No remarks provided.' }}
        </div>
    </div>

    <div class="remarks-section">
        <div class="remarks-label">Head Teacher's Remarks:</div>
        <div class="remarks-box">
            {{ $reportCard->headmaster_remarks ?? 'No remarks provided.' }}
        </div>
    </div>

    {{-- ========== GRADING KEY ========== --}}
    <table class="grading-key">
        <thead>
            <tr>
                <th colspan="6">GRADING SCALE</th>
            </tr>
        </thead>
        <tbody>
            <tr class="range-row">
                <td>80 &ndash; 100</td>
                <td>70 &ndash; 79</td>
                <td>60 &ndash; 69</td>
                <td>50 &ndash; 59</td>
                <td>40 &ndash; 49</td>
                <td>0 &ndash; 39</td>
            </tr>
            <tr class="desc-row">
                <td><strong class="grade-a">A</strong> — Excellent</td>
                <td><strong class="grade-b">B</strong> — Very Good</td>
                <td><strong class="grade-c">C</strong> — Good</td>
                <td><strong class="grade-d">D</strong> — Credit</td>
                <td><strong class="grade-e">E</strong> — Pass</td>
                <td><strong class="grade-f">F</strong> — Fail</td>
            </tr>
        </tbody>
    </table>

    {{-- ========== SIGNATURES ========== --}}
    <table class="signatures-table">
        <tr>
            <td>
                <div class="sig-line"></div><br>
                Class Teacher's Signature
                <div class="sig-date">Date: _______________</div>
            </td>
            <td>
                <div class="sig-line"></div><br>
                Head Teacher's Signature
                <div class="sig-date">Date: _______________</div>
            </td>
            <td>
                <div class="sig-line"></div><br>
                Parent/Guardian's Signature
                <div class="sig-date">Date: _______________</div>
            </td>
        </tr>
    </table>

    {{-- ========== FOOTER ========== --}}
    <div class="footer">
        This is a computer-generated report. &bull; Powered by DigiLearn &bull; Generated on
        {{ now()->format('jS F, Y') }}
        <br>
        <em>This report is confidential and intended solely for the student and their parent/guardian.</em>
    </div>

</body>

</html>