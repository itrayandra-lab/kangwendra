@extends('layouts.admin.app')
@section('title', $page)
@push('styles')
    <style>
        .submenu {
            margin-left: 20px;
            position: relative;
        }
        .submenu:before {
            content: "↳";
            position: absolute;
            left: -15px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 5px 10px;
            background: #f9f9f9;
            border-radius: 4px;
            margin-bottom: 5px;
            cursor: move;
            transition: background 0.3s;
        }
        .menu-item:hover {
            background: #e0e0e0;
        }

        .folder-icon:before {
            font-family: 'FontAwesome';
            content: "\f0c9"; 
            margin-right: 5px;
            color: #01e30c; 
        }
        .file-icon:before {
            font-family: 'FontAwesome';
            content: "\f0da"; 
            margin-right: 5px;
            color: #0056b3;  
        }

        .submenu .menu-item {
            background: #fff;
        }

        .order-controls {
            margin-left: auto;
            display: flex;
            gap: 5px;
        }
        .order-controls i {
            cursor: pointer;
            font-size: 14px;
            color: #007bff;
        }
        .order-controls i:hover {
            color: #0056b3;
        }

        .edit-icon {
            margin-left: 10px;
            cursor: pointer;
            color: #28a745;
        }
        .edit-icon:hover {
            color: #218838;
        }

        .delete-icon {
            margin-left: 10px;
            cursor: pointer;
            color: #dc3545;
        }
        .delete-icon:hover {
            color: #c82333;
        }

        .menu-item[draggable="true"] {
            user-select: none;
        }
        .drag-over {
            border: 2px dashed #007bff;
            background: #f1f1f1;
        }
        
        .slug:hover {
            cursor: pointer;
            font-weight: bold;
            color: #007bff;
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Management {{ $page }}</h3>
                </div>
                <div class="panel-body">
                    <div class="panel-action">
                        <a href="{{ route('menu.create') }}" class="btn btn-success btn-sm">
                            <i class="fa fa-plus"></i> Tambah Menu
                        </a>
                    </div>
                    <span>Daftar Menu</span>
                    <hr>
                    <div class="m-t-4">
                        <!-- Daftar Menu -->
                        <ul id="menu-list" style="list-style: none; padding-left: 0;">
                            @foreach($menus->where('type_1', 'parent')->sortBy('order') as $menu)
                                <li data-menu-id="{{ $menu->id }}" data-type_1="{{ $menu->type_1 }}" data-parent-id="{{ $menu->parent_id ?? 'null' }}" data-order="{{ $menu->order }}">
                                    <div class="menu-item folder-icon" draggable="true" data-slug="{{ $menu->slug }}">
                                        <span class="slug" onclick="window.location.href='{{ $menu->type_2 == 'page' ? url('page/'.$menu->slug) : url($menu->slug)}}'">{{ $menu->name }}</span>
                                        <div class="order-controls">
                                            <i class="fa fa-arrow-up" onclick="moveUp({{ $menu->id }})"></i>
                                            <i class="fa fa-arrow-down" onclick="moveDown({{ $menu->id }})"></i>
                                        </div>
                                        <i class="fa fa-edit edit-icon" onclick="editMenu({{ $menu->id }})"></i>
                                        <i class="fa fa-trash delete-icon" onclick="deleteMenu({{ $menu->id }})"></i>
                                    </div>
                                    <!-- Submenu -->
                                    @if($menus->where('parent_id', $menu->id)->count() > 0)
                                        <ul class="submenu-list" style="list-style: none;" data-parent-id="{{ $menu->id }}">
                                            @foreach($menus->where('parent_id', $menu->id)->sortBy('order') as $submenu)
                                                <li data-menu-id="{{ $submenu->id }}" data-type_1="{{ $submenu->type_1 }}" data-parent-id="{{ $submenu->parent_id ?? 'null' }}" data-order="{{ $submenu->order }}">
                                                    <div class="menu-item file-icon submenu" draggable="true" data-slug="{{ $submenu->slug }}">
                                                        <span class="slug" onclick="window.location.href='{{ $submenu->type_2 == 'page' ? url('page/'.$submenu->slug) : url($submenu->slug )}}'">{{ $submenu->name }}</span>
                                                        <div class="order-controls">
                                                            <i class="fa fa-arrow-up" onclick="moveUp({{ $submenu->id }})"></i>
                                                            <i class="fa fa-arrow-down" onclick="moveDown({{ $submenu->id }})"></i>
                                                        </div>
                                                        <i class="fa fa-edit edit-icon" onclick="editMenu({{ $submenu->id }})"></i>
                                                        <i class="fa fa-trash delete-icon" onclick="deleteMenu({{ $submenu->id }})"></i>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuItems = document.querySelectorAll('.menu-item[draggable="true"]');
            let draggedItem = null;

            // Drag and Drop
            menuItems.forEach(item => {
                item.addEventListener('dragstart', function (e) {
                    draggedItem = this.closest('li');
                    setTimeout(() => this.style.opacity = '0.4', 0);
                });

                item.addEventListener('dragend', function (e) {
                    this.style.opacity = '1';
                    draggedItem = null;
                });

                item.addEventListener('dragover', function (e) {
                    e.preventDefault();
                });

                item.addEventListener('drop', function (e) {
                    e.preventDefault();
                    const targetItem = this.closest('li');
                    if (!draggedItem || !targetItem) {
                        console.error('Dragged or Target item is null:', { draggedItem, targetItem });
                        return;
                    }

                    const draggedParentId = draggedItem.getAttribute('data-parent-id');
                    const targetParentId = targetItem.getAttribute('data-parent-id');
                    const draggedOrder = parseInt(draggedItem.getAttribute('data-order'));
                    const targetOrder = parseInt(targetItem.getAttribute('data-order'));
                    const isParentLevel = (draggedParentId === 'null' || draggedParentId === null || draggedParentId === '') && (targetParentId === 'null' || targetParentId === null || targetParentId === '');
                    const isSameSubmenu = draggedParentId === targetParentId && draggedParentId !== 'null' && draggedParentId !== null && draggedParentId !== '';
                    if (isParentLevel || isSameSubmenu) {
                        const orderData = [
                            { id: draggedItem.getAttribute('data-menu-id'), order: targetOrder },
                            { id: targetItem.getAttribute('data-menu-id'), order: draggedOrder }
                        ];

                        if (draggedOrder < targetOrder) {
                            targetItem.parentElement.insertBefore(draggedItem, targetItem.nextSibling);
                        } else {
                            targetItem.parentElement.insertBefore(draggedItem, targetItem);
                        }

                        draggedItem.setAttribute('data-order', targetOrder);
                        targetItem.setAttribute('data-order', draggedOrder);

                        updateOrder(orderData);
                    } else {
                        console.warn('Drag-and-drop not allowed between different levels');
                    }
                });

                item.addEventListener('dragenter', function (e) {
                    if (draggedItem) {
                        this.classList.add('drag-over');
                    }
                });

                item.addEventListener('dragleave', function (e) {
                    this.classList.remove('drag-over');
                });
            });

            // Fungsi untuk mengupdate order di backend
            function updateOrder(orderData) {
                fetch('{{ route('menu.updateOrder') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(orderData)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`Network response was not ok: ${response.status} - ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        console.log('Order updated successfully');
                    } else {
                        console.error('Update failed:', data.message);
                        alert('Gagal memperbarui urutan: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memperbarui urutan. Periksa console untuk detail.');
                });
            }

            // Fungsi untuk menggerakkan ke atas
            window.moveUp = function (menuId) {
                const item = document.querySelector(`[data-menu-id="${menuId}"]`);
                const prevItem = item.previousElementSibling;
                if (prevItem && prevItem.getAttribute('data-parent-id') === item.getAttribute('data-parent-id')) {
                    const currentOrder = parseInt(item.getAttribute('data-order'));
                    const prevOrder = parseInt(prevItem.getAttribute('data-order'));
                    const orderData = [
                        { id: item.getAttribute('data-menu-id'), order: prevOrder },
                        { id: prevItem.getAttribute('data-menu-id'), order: currentOrder }
                    ];
                    item.setAttribute('data-order', prevOrder);
                    prevItem.setAttribute('data-order', currentOrder);
                    item.parentElement.insertBefore(item, prevItem);
                    updateOrder(orderData);
                }
            };

            // Fungsi untuk menggerakkan ke bawah
            window.moveDown = function (menuId) {
                const item = document.querySelector(`[data-menu-id="${menuId}"]`);
                const nextItem = item.nextElementSibling;
                if (nextItem && nextItem.getAttribute('data-parent-id') === item.getAttribute('data-parent-id')) {
                    const currentOrder = parseInt(item.getAttribute('data-order'));
                    const nextOrder = parseInt(nextItem.getAttribute('data-order'));
                    const orderData = [
                        { id: item.getAttribute('data-menu-id'), order: nextOrder },
                        { id: nextItem.getAttribute('data-menu-id'), order: currentOrder }
                    ];
                    item.setAttribute('data-order', nextOrder);
                    nextItem.setAttribute('data-order', currentOrder);
                    item.parentElement.insertBefore(nextItem, item);
                    updateOrder(orderData);
                }
            };

            window.editMenu = function (menuId) {
                window.location.href = `/portal/menu/${menuId}/edit`;
            };

            window.deleteMenu = function (menuId) {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Menu ini akan dihapus secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/portal/menu/${menuId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                document.querySelector(`[data-menu-id="${menuId}"]`).remove();
                                Swal.fire(
                                    'Dihapus!',
                                    'Menu berhasil dihapus.',
                                    'success'
                                );
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    'Gagal menghapus menu.',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan saat menghapus menu.',
                                'error'
                            );
                        });
                    }
                });
            };
        });
    </script>
@endpush

