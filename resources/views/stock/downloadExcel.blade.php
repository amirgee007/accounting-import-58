<table>
    <thead>
    <tr>
        <th>Handle</th>
        <th>Variant Price</th>
        <th>Variant Taxable</th>
        <th>Stock</th>

    </tr>
    </thead>
    <tbody>

    @foreach($products as $index => $product)
        <tr>
            <td>{{$product['Handle']}}</td>
            <td>{{$product['Variant Price']}}</td>
            <td>FALSE</td>
            <td>{{$product['Stock']}}</td>
        </tr>
    @endforeach

    </tbody>
</table>
