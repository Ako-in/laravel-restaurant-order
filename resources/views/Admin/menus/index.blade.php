<a href="{{ route('admin.menus.create') }}"> Create New MENU</a>

<table>
   <tr>
       <th>ID</th>
       <th>Category ID</th>
       <th>Name</th>
       {{-- <th>Description</th> --}}
       <th>Price</th>
       {{-- <th>Image</th> --}}
       
       <th>Stock</th>
       <th>Status</th>
       <th>新商品</th>
       <th>おすすめ</th>
       {{-- <th>Category Name</th> --}}
       <th >Action</th>
   </tr>
   @foreach ($menus as $menu)
   <tr>
       <td>{{ $menu->id }}</td>
       <td>{{ $menu->category_id }}</td>
       <td>{{ $menu->name }}</td>
       {{-- <td>{{ $menu->description }}</td> --}}
       <td>{{ $menu->price }}</td>
       {{-- <th>{{$menu->image_file}}</th> --}}
       
       <td>{{ $menu->stock }}</td>
       {{-- <td>{{ $menu->status ? 'Active' : 'Inactive' }}</td> --}}
       <td>{{ $menu->status === 'active' ? 'Active' : 'Inactive' }}</td>
       <td>{{ $menu->is_new ? '⚪︎' : '-' }}</td>
       <td>{{ $menu->is_recommended ? '⚪︎' : '-' }}</td>
       <td>
            <form action="{{ route('admin.menus.destroy',$menu->id) }}" method="POST">
                <a href="{{ route('admin.menus.show',$menu->id) }}">Show</a>
                <a href="{{ route('admin.menus.edit',$menu->id) }}">Edit</a>
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
       </td>
   </tr>
   @endforeach
</table>