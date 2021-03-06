@extends('default.layouts.main')
@section('title', '文件管理')
@section('content')
    <div class="card mb-3">
        <div class="card-header">文件管理</div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col">
                    <a href="{{ route('admin.account.list') }}"
                       class="btn btn-sm btn-primary"> <i
                            class="ri-arrow-go-back-fill"></i> 返回</a>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col table-responsive">
                    <table class="table table-sm table-hover table-borderless">
                        <thead>
                        <tr>
                            <th scope="col">File</th>
                            <th scope="col" class="d-none d-md-block d-md-none">Size</th>
                            <th scope="col">Date</th>
                            <th scope="col">More</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!blank($path))
                            <tr onclick="window.location.href='{{ route('admin.file.manage', ['hash' => $hash, 'query' => url_encode(\App\Helpers\Tool::fetchGoBack($path))]) }}'">
                                <td colspan="4">
                                    <i class="ri-arrow-go-back-fill"></i> 返回上一层
                                </td>
                            </tr>
                        @endif
                        @if(blank($list))
                            <tr>
                                <td colspan="4" class="text-center">
                                    Ops! 暂无资源
                                </td>
                            </tr>
                        @else
                            @foreach($list as $data)
                                <tr class="list-item"
                                    data-route="{{ !array_has($data,'folder') ?'':route('admin.file.manage', ['hash' => $hash, 'query' => url_encode(implode('/', array_add($path, key(array_slice($path, -1, 1, true)) + 1, $data['name']) ))]) }}">
                                    <td style="text-overflow:ellipsis;overflow:hidden;white-space:nowrap;">
                                        <i class="ri-{{ \App\Helpers\Tool::fetchExtIco($data['ext'] ?? 'file') }}-fill"></i>
                                        {{ str_limit($data['name'], 32) }}
                                        @if($data['isHidden'])<i class="ri-eye-off-fill"></i>@endif
                                        @if($data['isLock'])<i class="ri-lock-fill"></i>@endif
                                    </td>

                                    <td class="d-none d-md-block d-md-none">{{ convert_size($data['size']) }}</td>
                                    <td>{{ date('Y-m-d H:i:s', strtotime($data['lastModifiedDateTime'])) }}</td>
                                    <td>
                                        <div class="btn-group dropdown" role="group">
                                            <button id="actionItem" type="button"
                                                    class="btn btn-primary btn-sm dropdown-toggle list-item-dropdown"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">更多
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="actionItem"
                                                 data-id="{{ $data['id'] }}" data-hash="{{ $hash }}"
                                                 data-etag="{{ $data['eTag'] ?? '' }}">
                                                <a class="dropdown-item delete-item" href="javascript:void(0)">删除</a>
                                                @if(array_has($data,'folder'))
                                                    <a class="dropdown-item encrypt-item"
                                                       href="javascript:void(0)">@if($data['isLock'])解除@endif加密</a>
                                                @endif
                                                <a class="dropdown-item hide-item"
                                                   href="javascript:void(0)"> @if($data['isHidden'])解除@endif隐藏</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        <tr>
                            <td colspan="4">
                                {{ array_get($item,'folder.childCount',0) }}
                                个项目
                                {{ convert_size(array_get($item,'size',0)) }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    {{ $list->links('default.components.page') }}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="btnOperate"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            操作此文件夹：
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnOperate" data-id="{{ $item['id'] }}"
                             data-hash="{{ $hash }}">
                            @if (blank($doc['readme']))
                                <a class="dropdown-item"
                                   href="{{ route('admin.file.create',['hash' => $hash, 'query' => $item['id'], 'fileName' => 'README.md']) }}">添加README</a>
                            @else
                                <a class="dropdown-item"
                                   href="{{ route('admin.file.edit', ['hash' => $hash, 'query' => $doc['readme']['id']]) }}">编辑README</a>
                            @endif
                            @if (blank($doc['head']))
                                <a class="dropdown-item"
                                   href="{{ route('admin.file.create',['hash' => $hash, 'query' => $item['id'], 'fileName' => 'HEAD.md']) }}">添加HEAD</a>
                            @else
                                <a class="dropdown-item"
                                   href="{{ route('admin.file.edit', ['hash' => $hash, 'query' => $doc['head']['id']]) }}">编辑HEAD</a>
                            @endif
                            <a class="dropdown-item encrypt-item"
                               href="#">@if($item['isLock'])解除@endif加密文件夹</a>
                            <a class="dropdown-item hide-item"
                               href="#">@if($item['isHidden'])解除@endif隐藏文件夹</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@push('scripts')
    <script>
        $(function() {
            $('.list-item').on('click', function(e) {
                if ($(this).attr('data-route')) {
                    window.location.href = $(this).attr('data-route')
                }
                e.stopPropagation()
            })
            $('.list-item-dropdown').on('mouseover', function(e) {
                $(this).dropdown({
                    boundary: 'window',
                })
                e.stopPropagation()
            })
            $('.delete-item').on('click', function(e) {
                Swal.fire({
                    title: '确定删除吗?',
                    text: '删除后无法恢复!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                }).then((result) => {
                    if (result.value) {
                        let source_id = $('.delete-item').parent().attr('data-id')
                        let hash = $('.delete-item').parent().attr('data-hash')
                        let eTag = $('.delete-item').parent().attr('data-etag')
                        axios.post("{{ route('admin.file.delete') }}", {
                            id: source_id,
                            hash: hash,
                            eTag: eTag,
                        })
                            .then(function(response) {
                                let data = response.data
                                if (data.error === '') {
                                    Swal.fire('删除成功！').then(() => {
                                        window.location.reload()
                                    })
                                } else {
                                    Swal.fire('删除失败！')
                                }
                            })
                            .catch(function(error) {
                                console.log(error)
                            })
                    }
                })
                e.stopPropagation()
            })
            $('.encrypt-item').on('click', function(e) {
                let id = $(this).parent().attr('data-id')
                let hash = $(this).parent().attr('data-hash')
                Swal.fire({
                    title: '请输入密码',
                    input: 'text',
                    inputValue: '123456',
                    showCancelButton: true,
                    inputValidator: (value) => {
                        if (!value) {
                            return '密码不能为空!'
                        }
                    },
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        console.log(result)
                        window.location.reload()
                    }
                    let password = result.value
                    axios.post("{{ route('admin.file.encrypt') }}", {
                        query: id,
                        hash: hash,
                        password: password,
                    })
                        .then(function(response) {
                            let data = response.data
                            if (data.error === '') {
                                Swal.fire('设置加密成功！').then(() => {
                                    window.location.reload()
                                })
                            } else {
                                Swal.fire('设置加密失败！')
                            }
                        })
                        .catch(function(error) {
                            console.log(error)
                        })
                })
                e.stopPropagation()
            })
            $('.hide-item').on('click', function(e) {
                let id = $(this).parent().attr('data-id')
                let hash = $(this).parent().attr('data-hash')
                axios.post("{{ route('admin.file.hide') }}", {
                    query: id,
                    hash: hash,
                })
                    .then(function(response) {
                        let data = response.data
                        if (data.error === '') {
                            Swal.fire('设置隐藏成功！').then(() => {
                                window.location.reload()
                            })
                        } else {
                            Swal.fire('设置隐藏失败！')
                        }
                    })
                    .catch(function(error) {
                        console.log(error)
                    })
                e.stopPropagation()
            })
        })
    </script>
@endpush
