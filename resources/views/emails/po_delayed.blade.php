<!DOCTYPE html>
<html>
<head>
    <title>PO Delay Alert</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2 style="color: #d9534f;">Purchase Order Delay Alert (Level {{ $level }})</h2>
    
    <p>The following Purchase Order has exceeded the expected delivery time and is not yet received.</p>
    
    <table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%;">
        <tr>
            <th style="background-color: #f8f9fa;">PO Number</th>
            <td>{{ $po->po_number }}</td>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa;">Vendor</th>
            <td>{{ $po->vendor->name }}</td>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa;">Order Date</th>
            <td>{{ $po->order_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <th style="background-color: #f8f9fa;">Expected Delivery</th>
            <td>
                {{ $po->expected_delivery_date ? $po->expected_delivery_date->format('d M Y') : 'N/A' }}
            </td>
        </tr>
    </table>

    <p>Please follow up with the vendor immediately.</p>
    
    <p style="font-size: 12px; color: gray;">
        This is an automated system alert. <br>
        Sent from Warehouse POS.
    </p>
</body>
</html>