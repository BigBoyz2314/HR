<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

date_default_timezone_set('Asia/Karachi');

// Default to current month/year if not provided
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

if ($month < 1 || $month > 12) $month = intval(date('m'));
if ($year < 2000 || $year > 2100) $year = intval(date('Y'));

$month_name = date('F', mktime(0, 0, 0, $month, 10));

require_once('includes/gen-salary-helper.php');

$stmt = "SELECT s.*, e.sNo, e.employee_code, e.allowance AS profile_allowance, e.department, e.designation 
         FROM salary1 s 
         LEFT JOIN employees e ON s.employeeID = e.employeeID 
         WHERE s.month = '$month' AND s.year = '$year' 
         ORDER BY CAST(COALESCE(NULLIF(e.sNo, 0), NULLIF(e.employee_code, ''), s.employeeID) AS UNSIGNED) ASC, s.employeeID ASC";
$result = $conn->query($stmt);

// Auto-generate if no records exist yet
if (!$result || $result->num_rows == 0) {
    autoGenerateSalarySheet($conn, $month, $year);
    $result = $conn->query($stmt);
}

$rows_data = [];
$totGross = 0; $totEarned = 0; $totArrears = 0; $totOt = 0;
$totAllow = 0; $totLoans = 0; $totAdv = 0; $totPayable = 0; $totPaid = 0; $totRem = 0;
$sr_no = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sr_no++;
        $fullName = trim($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']);
        $displayCode = !empty($row['sNo']) ? $row['sNo'] : (!empty($row['employee_code']) ? $row['employee_code'] : sprintf('%04d', $row['employeeID']));
        
        $basicSalary = floatval($row['basic_salary']);
        $profileAllowance = floatval($row['profile_allowance'] ?? 0);
        $grossSalary = $basicSalary + $profileAllowance;
        $payDays = intval($row['pay_days']);
        $earnedBasic = ($payDays / 30.0) * $basicSalary;
        
        $arrears = floatval($row['arrears'] ?? 0);
        $totalOt = floatval($row['ot_1_15'] ?? 0) + floatval($row['ot_16_30'] ?? 0);
        $allowanceAdv = floatval($row['allowance'] ?? 0);
        $lessLoans = floatval($row['less_loans'] ?? 0);
        $lessAdvance = floatval($row['less_advance'] ?? 0);
        
        $payable = floatval($row['payable']);
        $paid = floatval($row['paid']);
        $remaining = floatval($row['remaining']);

        $totGross += $grossSalary;
        $totEarned += $earnedBasic;
        $totArrears += $arrears;
        $totOt += $totalOt;
        $totAllow += $allowanceAdv;
        $totLoans += $lessLoans;
        $totAdv += $lessAdvance;
        $totPayable += $payable;
        $totPaid += $paid;
        $totRem += $remaining;

        $rows_data[] = [
            'sr' => $sr_no,
            'code' => $displayCode,
            'name' => $fullName,
            'gross' => $grossSalary,
            'days' => $payDays,
            'earned' => $earnedBasic,
            'arrears' => $arrears,
            'ot' => $totalOt,
            'allow' => $allowanceAdv,
            'loans' => $lessLoans,
            'advance' => $lessAdvance,
            'payable' => $payable,
            'paid' => $paid,
            'remaining' => $remaining
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Sheet Print - <?php echo $month_name . ' ' . $year; ?> - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 6mm 10mm 6mm;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
                font-size: 7.5pt !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .print-wrapper {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 7.5pt !important;
                border: 1px solid #000000 !important;
            }
            thead {
                display: table-header-group !important;
            }
            tfoot {
                display: table-footer-group !important;
            }
            tr {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
            th, td {
                border: 1px solid #475569 !important;
                padding: 3px 4px !important;
                color: #000000 !important;
                background: transparent !important;
            }
            th {
                background-color: #0f172a !important;
                color: #ffffff !important;
                font-weight: 700 !important;
                text-transform: uppercase !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            tfoot tr, tfoot td {
                background-color: #f1f5f9 !important;
                color: #000000 !important;
                font-weight: 800 !important;
                border-top: 2px solid #000000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .col-sign {
                width: 80px !important;
                min-width: 80px !important;
                text-align: center !important;
            }
            .sign-line {
                display: block !important;
                height: 18px !important;
                border-bottom: 1px dotted #475569 !important;
                margin: 4px auto 2px auto !important;
                width: 90% !important;
            }
        }
        @media screen {
            body {
                background-color: #f1f5f9;
            }
            .print-wrapper {
                max-width: 1280px;
                margin: 20px auto;
                background: #ffffff;
                padding: 24px 30px;
                border-radius: 12px;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
            }
            th, td {
                border: 1px solid #cbd5e1;
                padding: 5px 6px;
            }
            th {
                background-color: #0f172a;
                color: #ffffff;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 10px;
            }
            tfoot tr {
                background-color: #f8fafc;
                font-weight: 800;
                border-top: 2px solid #0f172a;
            }
            .col-sign {
                width: 90px;
                text-align: center;
            }
            .sign-line {
                display: block;
                height: 20px;
                border-bottom: 1px dotted #94a3b8;
                margin: 4px auto 2px auto;
                width: 85%;
            }
        }
    </style>
</head>
<body class="text-slate-900 antialiased font-sans">

    <!-- Top Action Bar (Screen Only) -->
    <div class="no-print bg-slate-900 text-white px-6 py-3 flex items-center justify-between shadow-md">
        <div class="flex items-center space-x-3">
            <a href="view-salary.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="text-slate-300 hover:text-white text-xs font-semibold flex items-center space-x-1.5 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Salary Statement</span>
            </a>
            <span class="text-slate-600">|</span>
            <span class="text-xs font-bold text-teal-400 uppercase tracking-wider">
                Print Preview &bull; <?php echo $month_name . ' ' . $year; ?>
            </span>
        </div>

        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="px-4 py-2 bg-teal-600 hover:bg-teal-500 text-white font-bold text-xs rounded-xl shadow transition flex items-center space-x-2">
                <i class="fa-solid fa-print"></i>
                <span>Print Document</span>
            </button>
            <button onclick="window.close()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs rounded-xl border border-slate-700 transition">
                Close
            </button>
        </div>
    </div>

    <!-- Printable Container -->
    <div class="print-wrapper">

        <!-- Official Header -->
        <div class="border-b-2 border-slate-900 pb-3 mb-3">
            <div class="flex items-center justify-between" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="text-xl font-black uppercase text-slate-900" style="font-size: 15pt; font-weight: 900; margin: 0; line-height: 1.2;">FOOTPRINT HR PORTAL</h1>
                    <h2 class="text-sm font-bold text-indigo-700" style="font-size: 10.5pt; font-weight: bold; margin: 2px 0;">Official Monthly Salary Statement &bull; <?php echo strtoupper($month_name . ' ' . $year); ?></h2>
                    <p class="text-xs text-slate-600" style="font-size: 8pt; margin: 0;">
                        Payroll Month: <strong><?php echo $month_name . ' ' . $year; ?></strong> &bull; 
                        Printed On: <strong><?php echo date('d-M-Y h:i A'); ?></strong> &bull; 
                        Generated By: <strong><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></strong> &bull; 
                        Total Active Staff: <strong><?php echo count($rows_data); ?></strong>
                    </p>
                </div>
                <div style="text-align: right;">
                    <img src="images/Footprint+Logo.png" alt="Footprint Logo" style="height: 38px; width: auto; max-height: 38px;">
                </div>
            </div>
        </div>

        <!-- Salary Statement Table -->
        <table id="salaryPrintTable">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">Sr.</th>
                    <th style="width: 40px; text-align: center;">Code</th>
                    <th style="text-align: left;">Employee Name</th>
                    <th style="text-align: right;">Gross Salary</th>
                    <th style="text-align: center;">Days</th>
                    <th style="text-align: right;">Salary for Month</th>
                    <th style="text-align: right;">Arrears</th>
                    <th style="text-align: right;">Overtime</th>
                    <th style="text-align: right;">Allowance + Adv.</th>
                    <th style="text-align: right;">Less Loans</th>
                    <th style="text-align: right;">Less Advance</th>
                    <th style="text-align: right;">Salary Payable</th>
                    <th style="text-align: right;">Paid</th>
                    <th style="text-align: right;">Remaining</th>
                    <th class="col-sign">Sign</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rows_data) > 0): ?>
                    <?php foreach ($rows_data as $r): ?>
                        <tr>
                            <td style="text-align: center; font-family: monospace; font-weight: bold; color: #475569;"><?php echo $r['sr']; ?></td>
                            <td style="text-align: center; font-family: monospace; font-weight: bold;"><?php echo htmlspecialchars($r['code']); ?></td>
                            <td style="text-align: left; font-weight: bold;"><?php echo htmlspecialchars($r['name']); ?></td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold;"><?php echo number_format($r['gross']); ?></td>
                            <td style="text-align: center; font-weight: bold;"><?php echo $r['days']; ?></td>
                            <td style="text-align: right; font-family: monospace;"><?php echo number_format($r['earned']); ?></td>
                            <td style="text-align: right; font-family: monospace;"><?php echo $r['arrears'] > 0 ? number_format($r['arrears']) : '-'; ?></td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold;"><?php echo $r['ot'] > 0 ? number_format($r['ot']) : '-'; ?></td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold;"><?php echo $r['allow'] > 0 ? number_format($r['allow']) : '-'; ?></td>
                            <td style="text-align: right; font-family: monospace; color: #b91c1c;"><?php echo $r['loans'] > 0 ? '-' . number_format($r['loans']) : '-'; ?></td>
                            <td style="text-align: right; font-family: monospace; color: #b91c1c;"><?php echo $r['advance'] > 0 ? '-' . number_format($r['advance']) : '-'; ?></td>
                            <td style="text-align: right; font-family: monospace; font-weight: 900;"><?php echo number_format($r['payable']); ?></td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold; color: #047857;"><?php echo number_format($r['paid']); ?></td>
                            <td style="text-align: right; font-family: monospace; font-weight: bold; <?php echo $r['remaining'] > 0 ? 'color: #b91c1c;' : ''; ?>">
                                <?php echo number_format($r['remaining']); ?>
                            </td>
                            <!-- Dedicated Space for Sign -->
                            <td class="col-sign">
                                <div class="sign-line"></div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="15" style="text-align: center; padding: 25px; color: #64748b; font-weight: bold;">
                            No salary records found for <?php echo $month_name . ' ' . $year; ?>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (count($rows_data) > 0): ?>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: left;">Total (<?php echo count($rows_data); ?> Staff)</td>
                        <td style="text-align: right; font-family: monospace;"><?php echo number_format($totGross); ?></td>
                        <td style="text-align: center;">-</td>
                        <td style="text-align: right; font-family: monospace;"><?php echo number_format($totEarned); ?></td>
                        <td style="text-align: right; font-family: monospace;"><?php echo number_format($totArrears); ?></td>
                        <td style="text-align: right; font-family: monospace;"><?php echo number_format($totOt); ?></td>
                        <td style="text-align: right; font-family: monospace;"><?php echo number_format($totAllow); ?></td>
                        <td style="text-align: right; font-family: monospace;"><?php echo number_format($totLoans); ?></td>
                        <td style="text-align: right; font-family: monospace;"><?php echo number_format($totAdv); ?></td>
                        <td style="text-align: right; font-family: monospace; font-weight: 900;"><?php echo number_format($totPayable); ?></td>
                        <td style="text-align: right; font-family: monospace;"><?php echo number_format($totPaid); ?></td>
                        <td style="text-align: right; font-family: monospace;"><?php echo number_format($totRem); ?></td>
                        <td class="col-sign"></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>

        <!-- Print Authorization Signatures -->
        <div class="pt-8 mt-6" style="page-break-inside: avoid; break-inside: avoid;">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2.5rem; text-align: center;">
                <div>
                    <div style="border-bottom: 1.5px solid #000; margin-bottom: 6px; padding-bottom: 35px;"></div>
                    <span style="font-weight: bold; font-size: 10px; text-transform: uppercase; color: #000;">Prepared By (HR / Accounts)</span>
                </div>
                <div>
                    <div style="border-bottom: 1.5px solid #000; margin-bottom: 6px; padding-bottom: 35px;"></div>
                    <span style="font-weight: bold; font-size: 10px; text-transform: uppercase; color: #000;">Verified / Checked By</span>
                </div>
                <div>
                    <div style="border-bottom: 1.5px solid #000; margin-bottom: 6px; padding-bottom: 35px;"></div>
                    <span style="font-weight: bold; font-size: 10px; text-transform: uppercase; color: #000;">Approved By (Director / Management)</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Auto Print Trigger -->
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 350);
        });
    </script>
</body>
</html>
