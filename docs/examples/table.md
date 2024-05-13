## Table
```html
<table>
    <thead>
        <tr>
            <th>id</th>
            <th>title</th>
            <th>content</th>
            <th>user_id</th>
            <th>sort_number</th>
            <th>is_active</th>
            <th>created_at</th>
            <th>updated_at</th>
            <th>deleted_at</th>
        </tr>
    </thead>
    <tbody>
    @foreach($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>{{ $product->title }}</td>
            <td>{{ $product->content }}</td>
            <td>{{ $product->user_id }}</td>
            <td>{{ $product->sort_number }}</td>
            <td>{{ $product->is_active }}</td>
            <td>{{ $product->created_at }}</td>
            <td>{{ $product->updated_at }}</td>
            <td>{{ $product->deleted_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
```