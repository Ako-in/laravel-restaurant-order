<div>
  <h2> Show Menu</h2>
</div>
<div>
  <a href="{{ route('admin.menus.index') }}"> Back</a>
</div>

<div>
  <strong>id:</strong>
  {{$menu->id}}
</div>

<div>
  <strong>Name:</strong>
  {{$menu->name}}
</div>

<div>
  <strong>CategoryName:</strong>
  {{$menu->category_id}}
</div>

<div>
  <strong>Description:</strong>
  {{$menu->description}}
</div>

<div>
  <strong>Status:</strong>
  {{ $menu->status ? 'Active' : 'Inactive' }}
</div>

<div>
  <strong>Stock:</strong>
  {{$menu->stock}} 
</div>

<div>
  <strong>Price:</strong>
  {{$menu->price}} 
</div>



<div>
  <strong>新商品フラグ:</strong>
  {{$menu->is_new ? '⚪︎' : '-'}} 
</div>

<div>
  <strong>おすすめフラグ:</strong>
  {{$menu->is_recommended ? '⚪︎' : '-'}} 
</div>

{{-- <div class="container mb-2">
  <div class="w-48 h-48 overflow-hidden flex items-center justify-center mx-auto rounded-lg">

    @if ($menu->image_file !== '')
      <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image" class="flex-shrink-0 min-w-full min-h-full object-cover">
    @else
        <img src="{{ asset('storage/images/noimage.png') }}" class="flex-shrink-0 min-w-full min-h-full object-cover">
    @endif
  </div>
  
</div> --}}
<div class="container mb-2">
  <div class="w-48 h-48 overflow-hidden flex items-center justify-center mx-auto rounded-lg">

    @if ($menu->image_file !== '')
      <img src="{{ asset('storage/' . $menu->image_file) }}" alt="Menu Image" class="flex-shrink-0 min-w-full min-h-full object-cover">
    @else
        <img src="{{ asset('storage/images/noimage.png') }}" class="flex-shrink-0 min-w-full min-h-full object-cover">
    @endif
  </div>
  
</div>

<div>
  <strong>登録日:</strong>
  {{$menu->created_at}} 
</div>

<div>
  <strong>更新日:</strong>
  {{$menu->updated_at}} 
</div>

<div>
  <a href="{{ route('admin.menus.edit',$menu->id) }}">Edit</a>
</div>  
