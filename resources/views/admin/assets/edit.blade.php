@extends('layouts.admin')

@section('title', 'Edit Asset')
@section('page-title', 'Edit Asset: '.$asset->name)

@section('content')
<div class="card shadow-sm border-0"><div class="card-body"><form method="POST" action="{{ route('admin.assets.update', $asset) }}">@method('PUT') @include('admin.assets._form', ['submitLabel' => 'Update Asset'])</form></div></div>
@endsection