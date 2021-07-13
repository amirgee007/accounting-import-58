<table>
    <thead>
    <tr>
        <th>Id</th>
        <th>Codigo</th>
        <th>Message</th>
        <th>Created</th>
    </tr>
    </thead>
    <tbody>

    @foreach($errors as $index => $product)
        <tr>
            <td>{{$product['id']}}</td>
            <td>{{$product['codigo_number']}}</td>
            <td>{{$product['message']}}</td>
            <td>{{$product['created_at']}}</td>
        </tr>
    @endforeach

    </tbody>
</table>
