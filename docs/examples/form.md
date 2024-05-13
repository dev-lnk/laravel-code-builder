## Html form
```html
<form action="{{ route('products.store') }}" method="POST">
    @csrf
	<div>
            <label for="title">title</label>
            <input id="title" name="title" value="{{ old('title') }}"/>
	</div>
	<div>
            <label for="content">content</label>
            <input id="content" name="content" value="{{ old('content') }}"/>
	</div>
	<div>
            <label for="user_id">user_id</label>
            <select id="user_id" name="user_id">
                <option value="">Not selected</option>
            </select>
	</div>
	<div>
            <label for="sort_number">sort_number</label>
            <input id="sort_number" name="sort_number" value="{{ old('sort_number') }}" type="number"/>
	</div>
	<div>
            <label for="is_active">is_active</label>
            <input type="checkbox" id="is_active" name="is_active" value="1" @if(old('is_active')) checked @endif/>
	</div>
    <button type="submit">Submit</button>
</form>
```