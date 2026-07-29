<?php
require_once('includes/config.php');
init_hr_session();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = "SELECT l.*, e.fname, e.mname, e.lname, e.department, e.employee_code 
         FROM employee_ledger l 
         LEFT JOIN employees e ON l.employeeID = e.employeeID 
         WHERE l.id = '$id'";
$result = $conn->query($stmt);

if (!$result || $result->num_rows == 0) {
    echo "Payment voucher not found.";
    exit;
}

$row = $result->fetch_assoc();
$typeLower = strtolower($row['type']);

// Block vouchers for adjustments, reductions, or non-disbursements
if (strpos($typeLower, 'adjustment') !== false || strpos($typeLower, 'reduction') !== false || ($row['type'] !== 'Salary Payment' && $row['type'] !== 'Advance Payment')) {
    echo "<div style='padding:40px; font-family:sans-serif; text-align:center;'><h2>No Voucher Available</h2><p style='color:#64748b;'>Payment vouchers are only generated for direct Salary and Advance disbursements, not accounting adjustments or reductions.</p><p><a href='employee-ledger.php?id=" . $row['employeeID'] . "' style='color:#4f46e5; font-weight:bold;'>Return to Employee Ledger</a></p></div>";
    exit;
}

$empName = trim($row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']);
$empCode = !empty($row['employee_code']) ? $row['employee_code'] : sprintf('%04d', $row['employeeID']);
$monthName = ($row['month'] > 0) ? date('F', mktime(0,0,0,$row['month'],1)) . ' ' . $row['year'] : '-';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher #VCH-<?php echo sprintf('%05d', $row['id']); ?> - Footprint HR</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        @page {
            size: 80mm auto;
            margin: 0mm !important;
        }
        @media print {
            html, body {
                width: 80mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                color: black !important;
                font-size: 13px !important;
                display: block !important;
                min-height: 0 !important;
                height: auto !important;
            }
            .voucher-card {
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 !important;
                padding: 2mm 3mm !important;
                box-shadow: none !important;
                border: none !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-mono text-slate-900 min-h-screen flex flex-col items-center justify-start p-4">

    <!-- Action Bar (Hidden when printing) -->
    <div class="no-print mb-4 flex items-center space-x-3">
        <button onclick="window.print()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-lg transition flex items-center space-x-2">
            <i class="fa-solid fa-print"></i>
            <span>Print Thermal Voucher (80mm)</span>
        </button>
        <a href="employee-ledger.php?id=<?php echo $row['employeeID']; ?>" class="px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold rounded-xl text-xs transition">
            Back to Ledger
        </a>
    </div>

    <!-- 80mm Thermal Receipt Voucher Card -->
    <div class="voucher-card w-[85mm] bg-white p-5 shadow-xl border border-slate-300 text-sm leading-normal space-y-4 font-mono text-slate-900">
        
        <!-- Header -->
        <div class="text-center border-b-2 border-dashed border-slate-400 pb-3">
            <h2 class="font-black text-lg uppercase tracking-wider text-slate-900">FOOTPRINT HR</h2>
            <p class="text-xs font-bold text-slate-700 uppercase tracking-widest font-sans mt-0.5">Payment Voucher</p>
            <div class="mt-2 text-xs font-extrabold text-indigo-900">
                Voucher #: <span class="font-mono">VCH-<?php echo sprintf('%05d', $row['id']); ?></span>
            </div>
            <p class="text-xs text-slate-600 font-semibold mt-0.5"><?php echo date('d-M-Y h:i A', strtotime($row['trans_date'])); ?></p>
        </div>

        <!-- Employee Info -->
        <div class="space-y-1.5 text-xs border-b-2 border-dashed border-slate-400 pb-3 font-semibold">
            <div class="flex justify-between">
                <span class="text-slate-600">Employee ID:</span>
                <span class="font-extrabold text-slate-900">#<?php echo htmlspecialchars($empCode); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Name:</span>
                <span class="font-extrabold text-slate-900"><?php echo htmlspecialchars($empName); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Dept:</span>
                <span class="font-bold text-slate-800"><?php echo htmlspecialchars($row['department'] ?: 'General'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-600">Period:</span>
                <span class="font-extrabold text-slate-900"><?php echo $monthName; ?></span>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="space-y-2 border-b-2 border-dashed border-slate-400 pb-3">
            <div class="flex justify-between items-center text-xs">
                <span class="text-slate-600">Type:</span>
                <span class="font-extrabold uppercase text-xs bg-slate-100 px-2 py-0.5 rounded border border-slate-400"><?php echo htmlspecialchars($row['type']); ?></span>
            </div>
            <div class="flex justify-between items-center text-xs">
                <span class="text-slate-600">Mode:</span>
                <span class="font-bold text-slate-900"><?php echo htmlspecialchars($row['payment_method'] ?: 'Cash'); ?></span>
            </div>
            <?php if (!empty($row['description'])): ?>
                <div class="text-xs text-slate-700 font-medium italic bg-slate-50 p-1.5 rounded border border-slate-200">
                    Note: <?php echo htmlspecialchars($row['description']); ?>
                </div>
            <?php endif; ?>

            <div class="pt-2 flex justify-between items-center text-base font-black border-t-2 border-slate-400">
                <span>Amount Paid:</span>
                <span class="text-slate-900">PKR <?php echo number_format(round($row['debit'] > 0 ? $row['debit'] : $row['credit'])); ?></span>
            </div>
        </div>

        <!-- Balance Info -->
        <div class="text-xs space-y-1">
            <div class="flex justify-between font-extrabold text-slate-900">
                <span>Ledger Balance:</span>
                <span>PKR <?php echo number_format(round($row['balance'])); ?></span>
            </div>
            <p class="text-[10px] text-slate-600 text-center font-semibold italic mt-1">
                <?php echo $row['balance'] < 0 ? '* Advance Balance Overpaid' : ($row['balance'] > 0 ? '* Pending Payable Balance' : '* Fully Settled'); ?>
            </p>
        </div>

        <!-- Signatures -->
        <div class="pt-10 grid grid-cols-2 gap-4 text-xs font-bold text-center">
            <div class="border-t-2 border-slate-400 pt-1">
                <span>Receiver Signature</span>
            </div>
            <div class="border-t-2 border-slate-400 pt-1">
                <span>Authorized Sign</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-[10px] text-slate-500 font-semibold pt-3 border-t border-slate-200">
            Thank you! Powered by Footprint HR System
        </div>
    </div>

</body>
</html>
