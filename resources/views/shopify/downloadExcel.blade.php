<table>
    <thead>
    <tr>
        <th>Handle</th>
        <th>Title</th>
        <th>Body (HTML)</th>
        <th>Vendor</th>
        <th>Type</th>
        <th>Tags</th>
        <th>Published</th>
        <th>Option1 Name</th>
        <th>Option1 Value</th>
        <th>Option2 Name</th>
        <th>Option2 Value</th>
        <th>Variant SKU</th>
        <th>Variant Inventory Tracker</th>
        <th>Variant Inventory Qty</th>
        <th>Variant Inventory Policy</th>
        <th>Variant Price</th>
        <th>Variant Requires Shipping</th>
        <th>Variant Taxable</th>
    </tr>
    </thead>
    <tbody>

    @foreach($products as $index => $product)
        <tr>
            <td>{{$product['Handle']}}</td>
            <td>{{$product['Title']}}</td>
            <td>{{$product['Body']}}</td>
            <td>{{$product['Vendor']}}</td>
            <td>{{$product['Type']}}</td>
            <td>{{$product['Tags']}}</td>
            <td>FALSE</td>

            <td>{{$product['Option1_Name']}}</td>
            <td>{{$product['Option1_Value']}}</td>
            <td>{{$product['Option2_Name']}}</td>
            <td>{{$product['Option2_Value']}}</td>

            <td>{{$product['Variant_SKU']}}</td>
            <td>{{$product['V_Inventory_Tracker']}}</td>
            <td>{{$product['V_Inventory_Qty']}}</td>
            <td>{{$product['V_Inventory_Policy']}}</td>
            <td>{{$product['V_Price']}}</td>
            <td>{{$product['V_Requires_Shipping'] ? 'TRUE' : 'FALSE'}}</td>
            <td>{{$product['V_Taxable'] ? 'TRUE' : 'FALSE'}}</td>
        </tr>
    @endforeach

    </tbody>
</table>
