<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once('includes/config.php');

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
            margin: 0mm;
        }
        @media print {
            body {
                width: 80mm;
                margin: 0 auto;
                padding: 4mm;
                background: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-mono text-slate-900 min-h-screen flex flex-col items-center justify-center p-4">

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
    <div class="w-[80mm] bg-white p-4 shadow-xl border border-slate-200 text-xs leading-tight space-y-3 font-mono text-slate-900">
        
        <!-- Header -->
        <div class="text-center border-b border-dashed border-slate-400 pb-3">
            <h2 class="font-extrabold text-sm uppercase tracking-wider">FOOTPRINT HR</h2>
            <p class="text-[10px] text-slate-600 uppercase font-sans">Payment Voucher</p>
            <div class="mt-2 text-[10px] font-bold">
                Voucher #: <span class="font-mono">VCH-<?php echo sprintf('%05d', $row['id']); ?></span>
            </div>
            <p class="text-[10px] text-slate-500"><?php echo date('d-M-Y h:i A', strtotime($row['trans_date'])); ?></p>
        </div>

        <!-- Employee Info -->
        <div class="space-y-1 text-[11px] border-b border-dashed border-slate-400 pb-2">
            <div class="flex justify-between">
                <span class="text-slate-500">Employee ID:</span>
                <span class="font-bold">#<?php echo htmlspecialchars($empCode); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Name:</span>
                <span class="font-bold"><?php echo htmlspecialchars($empName); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Dept:</span>
                <span><?php echo htmlspecialchars($row['department'] ?: 'General'); ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Period:</span>
                <span class="font-bold"><?php echo $monthName; ?></span>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="space-y-1.5 border-b border-dashed border-slate-400 pb-3">
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Type:</span>
                <span class="font-bold uppercase text-[10px] bg-slate-100 px-1.5 py-0.5 rounded border border-slate-300"><?php echo htmlspecialchars($row['type']); ?></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Mode:</span>
                <span class="font-semibold"><?php echo htmlspecialchars($row['payment_method'] ?: 'Cash'); ?></span>
            </div>
            <?php if (!empty($row['description'])): ?>
                <div class="text-[10px] text-slate-600 italic">
                    Note: <?php echo htmlspecialchars($row['description']); ?>
                </div>
            <?php endif; ?>

            <div class="pt-2 flex justify-between items-center text-sm font-extrabold border-t border-slate-300">
                <span>Amount Paid:</span>
                <span>PKR <?php echo number_format($row['debit'] > 0 ? $row['debit'] : $row['credit'], 2); ?></span>
            </div>
        </div>

        <!-- Balance Info -->
        <div class="text-[11px] space-y-1">
            <div class="flex justify-between font-bold">
                <span>Ledger Balance:</span>
                <span>PKR <?php echo number_format($row['balance'], 2); ?></span>
            </div>
            <p class="text-[9px] text-slate-500 text-center italic mt-1">
                <?php echo $row['balance'] < 0 ? '* Advance Balance Overpaid' : ($row['balance'] > 0 ? '* Pending Payable Balance' : '* Fully Settled'); ?>
            </p>
        </div>

        <!-- Signatures -->
        <div class="pt-8 grid grid-cols-2 gap-4 text-[9px] text-center">
            <div class="border-t border-slate-400 pt-1">
                <span>Receiver Signature</span>
            </div>
            <div class="border-t border-slate-400 pt-1">
                <span>Authorized Sign</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-[8px] text-slate-400 pt-2 border-t border-slate-200">
            Thank you! Powered by Footprint HR System
        </div>
    </div>

    <script>
        // Auto print popup on page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 300);
        });
    </script>
</body>
</html>
