<?php include "header.php"; ?>
<?php

//   ini_set("display_errors",true);
//   error_reporting(E_ALL);
  
  if (isset($_POST['create'])) {
      
  $name =  $_POST['name'];
  $mobile =  $_POST['mobile'];
  $remark = $_POST['remark'];
  $amount =  $_POST['amount'];
  
if($remark == ''){
    $remark = 'Your Payment Link is Created';
}
      
      
    
    $orderid = mt_rand(10000000000,9999999999999);
       
    $data = array(
    'customer_mobile' => $mobile,
    'user_token' => $userdata["user_token"],
    'amount' => $amount,
    'order_id' => $orderid,
    'redirect_url' => 'https://'.$_SERVER["SERVER_NAME"].'/success',
    'remark1' => $remark,
    'remark2' => "payment_link",
);
  
        $curl = curl_init();

curl_setopt_array($curl, array(
   CURLOPT_URL => 'https://'.$_SERVER["SERVER_NAME"].'/api/create-order',
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_ENCODING => '',
   CURLOPT_MAXREDIRS => 10,
   CURLOPT_TIMEOUT => 0,
   CURLOPT_FOLLOWLOCATION => true,
   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
   CURLOPT_CUSTOMREQUEST => 'POST',
   CURLOPT_POSTFIELDS => http_build_query($data),
   CURLOPT_HTTPHEADER => array(
      'User-Agent: Apidog/1.0.0 (https://apidog.com)'
   ),
));

$response = curl_exec($curl);


curl_close($curl);

$jsondatares = json_decode($response,true);
 
      $paymentlink = '';
       if($jsondatares["status"] == true){
      $paymentlink = $jsondatares["result"]["payment_url"];
       }else{
            echo '
    <script>
        Swal.fire({
            title: "Opps! Failed To Create Payment Link!",
            text: "'.$jsondatares["message"].'",
            confirmButtonText: "Ok",
            icon: "error"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "payment_link"; // Replace with your desired redirect URL
            }
        });
    </script>
';
exit;
       }
  }

  ?>
  
  
<div class="container mx-auto p-6">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Create Payment Link</h1>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700">Customer Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        placeholder="Your Name" 
                        class="w-full p-2 border rounded-lg" 
                        required 
                        pattern="^[A-Za-z\s]{2,50}$" 
                        title="Only letters and spaces, 2-50 characters">
                </div>
                <div>
                    <label class="block text-gray-700">Mobile Number</label>
                    <input 
                        type="tel" 
                        name="mobile" 
                        placeholder="Your Mobile" 
                        class="w-full p-2 border rounded-lg" 
                        required 
                        pattern="^[6-9]\d{9}$" 
                        title="Enter a valid 10-digit Indian mobile number starting with 6-9">
                </div>
                <div>
                    <label class="block text-gray-700">Amount (INR)</label>
                    <input 
                        type="number" 
                        name="amount" 
                        placeholder="₹0.00" 
                        class="w-full p-2 border rounded-lg" 
                        required 
                        min="1" 
                        step="0.01" 
                        title="Amount must be at least ₹1">
                </div>
                <div>
                    <label class="block text-gray-700">Remark</label>
                    <input 
                        type="text" 
                        name="remark" 
                        placeholder="Remarks Eg. Gift, Deposit etc." 
                        class="w-full p-2 border rounded-lg" 
                        pattern="^[A-Za-z\s]{2,50}$" 
                        title="Only letters and spaces, 2-50 characters"
                        maxlength="100">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" name="create" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Submit</button>
            </div>
        </form>
    </div>
</div>

        
        <div class="bg-white p-6 rounded-lg shadow-md mt-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">List Of Payment Link</h2>
            <form method="GET" class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-gray-700">From Date</label>
                        <input type="date" name="from_date" class="p-2 border rounded-lg w-full" value="<?php echo isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <label class="block text-gray-700">To Date</label>
                        <input type="date" name="to_date" class="p-2 border rounded-lg w-full" value="<?php echo isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d'); ?>">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Filter</button>
                    </div>
                </div>
            </form>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 border-b">
                            <th class="p-2 border">#</th>
                            <th class="p-2 border">User ID</th>
                            <th class="p-2 border">Customer Mobile</th>
                            <th class="p-2 border">Amount</th>
                            <th class="p-2 border">Order ID</th>
                            <th class="p-2 border">Status</th>
                            <th class="p-2 border">Remarks</th>
                            <th class="p-2 border">Date</th>
                        </tr>
                    </thead>
                    <tbody id="paymentTable">
                        <?php
                        $from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d');
                        $to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');
                        
                        $query = "SELECT * FROM `orders` WHERE user_id='{$userdata['id']}' AND DATE(create_date) BETWEEN '$from_date' AND '$to_date' AND remark2 = 'payment_link' ORDER BY `id` DESC LIMIT 25";
                        $query_run = mysqli_query($conn, $query);

                        if ($query_run) {
                            while ($row = mysqli_fetch_assoc($query_run)) {
                                $statusBadge = ($row['status'] == 'SUCCESS') ? '<span class="text-green-500">Success</span>' : (($row['status'] == 'FAILURE') ? '<span class="text-red-500">Failed</span>' : '<span class="text-yellow-500">Pending</span>');
                                echo "<tr class='border-b'>";
                                echo "<td class='p-2 border'>" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td class='p-2 border'>" . htmlspecialchars($row['user_id'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td class='p-2 border'>" . htmlspecialchars($row['customer_mobile'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td class='p-2 border'>₹" . htmlspecialchars($row['amount'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td class='p-2 border'>" . htmlspecialchars($row['order_id'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td class='p-2 border'>" . $statusBadge . "</td>";
                                echo "<td class='p-2 border'>" . htmlspecialchars($row['remark1'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "<td class='p-2 border'>" . htmlspecialchars($row['create_date'], ENT_QUOTES, 'UTF-8') . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='p-2 border text-center text-red-500'>Error in query: " . mysqli_error($conn) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
   <?php if($paymentlink != '') { ?>
    <div id="modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <h4 class="text-lg font-bold text-blue-600 mb-4">Payment Link Created Successfully</h4>
            <div class="flex items-center border p-2 rounded mb-4">
                <input onclick="copy()" class="flex-1 p-1 text-gray-700" value="<?php echo $paymentlink ?>" id="copyClipboard" readonly>
                <button class="ml-2 px-2 py-1 bg-gray-200 rounded" onclick="copy()"><i class="fa fa-copy"></i></button>
            </div>
            <p class="text-gray-600 text-sm">This Payment Link is valid for only 10 min.</p>
            <div class="flex justify-end mt-4">
                <button class="px-4 py-2 bg-gray-400 text-white rounded mr-2" onclick="closeModal()">Cancel</button>
                <button class="px-4 py-2 bg-blue-600 text-white rounded" onclick="copy()">Copy</button>
                <button class="px-4 py-2 bg-blue-600 text-white rounded" onclick="openDialog('<?php echo $paymentlink ?>');">Open</button>
            </div>
        </div>
    </div>
<script>
    function copy() {
        let copyText = document.getElementById("copyClipboard");
        navigator.clipboard.writeText(copyText.value);
        alert("Link Copied!");
    }

    function closeModal() {
        // Hide the modal by setting its display to "none"
        document.getElementById("modal").style.display = "none";
    }
</script>
<?php } ?>
