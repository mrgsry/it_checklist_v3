@extends('layouts.user')

@section('title', 'Edit Asset')
@section('page-title', 'Edit Asset: '.$asset->name)

@section('content')
<div class="card shadow-sm border-0"><div class="card-body"><form method="POST" action="{{ route('user.assets.update', $asset) }}">@method('PUT') @include('admin.assets._form', ['asset' => $asset, 'submitLabel' => 'Update Asset', 'cancelRoute' => route('user.assets.index')])</form></div></div>
@endsection