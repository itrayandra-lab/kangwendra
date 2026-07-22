<?php

use App\Http\Middleware\IsActive;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdsController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PostCategories;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\MenusController;
use App\Http\Controllers\Admin\PostsController;
use App\Http\Controllers\Admin\VideosController;
use App\Http\Controllers\Admin\UploadImageEditor;
use App\Http\Controllers\Admin\PostTagsController;
use App\Http\Controllers\Client\InterfaceController;
use App\Http\Controllers\Admin\DomainShareController;
use App\Http\Controllers\Admin\InformationController;
use App\Http\Controllers\Admin\WebIdentityController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\RefArticleController;
use App\Http\Controllers\Admin\RssController;

# Auth
Route::group(['prefix' => 'portal', 'controller' => LoginController::class], function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login')->name('portal.login.submit');
    Route::post('/logout', 'logout')->name('portal.logout');
});

# Portal
Route::group(['prefix' => 'portal', 'middleware' => ['auth']], function () {

    Route::get('home', [HomeController::class, 'index'])->name('admin.dashboard');

    # Image Handler
    Route::group(['prefix' => 'image', 'controller' => UploadImageEditor::class], function () {
        Route::post('upload-image', 'uploadImage')->name('uploadImage')->middleware('throttle:20,1');
        Route::post('delete-image', 'deleteImage')->name('deleteImage')->middleware('throttle:10,1');
    });

    # User management
    Route::group(['prefix' => 'users', 'controller' => UserController::class], function () {
        Route::get('/', 'index')->name('users.index')->middleware('permission:view users');
        Route::get('/create', 'create')->name('users.create')->middleware('permission:create users');
        Route::post('/', 'store')->name('users.store')->middleware('permission:create users');
        Route::get('/{user}/edit', 'edit')->name('users.edit')->middleware('permission:edit users');
        Route::put('/{user}', 'update')->name('users.update')->middleware('permission:edit users');
        Route::delete('/{user}', 'destroy')->name('users.destroy')->middleware('permission:delete users');

        #update personal user
        Route::get('profil', 'profil')->name('users.profil');
        Route::put('profil/update', 'profilUpdate')->name('users.profilUpdate');
    });

    # Role management
    Route::group(['prefix' => 'roles', 'controller' => RolePermissionController::class, 'middleware' => ['auth', 'permission:manage roles']], function () {
        Route::get('/', 'indexRoles')->name('roles.index');
        Route::get('/create', 'createRole')->name('roles.create');
        Route::post('/', 'storeRole')->name('roles.store');
        Route::get('/{role}/edit', 'editRole')->name('roles.edit');
        Route::put('/{role}', 'updateRole')->name('roles.update');
        Route::delete('/{role}', 'destroyRole')->name('roles.destroy');
    });

    # Permission management
    Route::group(['prefix' => 'permissions', 'controller' => RolePermissionController::class, 'middleware' => ['auth', 'permission:manage permissions']], function () {
        Route::get('/', 'indexPermissions')->name('permissions.index');
        Route::get('/create', 'createPermission')->name('permissions.create');
        Route::post('/', 'storePermission')->name('permissions.store');
        Route::get('/{permission}/edit', 'editPermission')->name('permissions.edit');
        Route::put('/{permission}', 'updatePermission')->name('permissions.update');
        Route::delete('/{permission}', 'destroyPermission')->name('permissions.destroy');
    });

    # Identity Web
    Route::group(['prefix' => 'web-identities', 'controller' => WebIdentityController::class, 'middleware' => ['auth', 'permission:manage web identities']], function () {
        Route::get('/', 'index')->name('web-identity.index');
        Route::post('/', 'storeOrUpdate')->name('web-identity.store-or-update');
    });

    # Page management
    Route::group(['prefix' => 'pages', 'controller' => PageController::class], function () {
        Route::get('/', 'index')->name('pages.index')->middleware('permission:view pages');
        Route::get('/create', 'create')->name('pages.create')->middleware('permission:create pages');
        Route::post('/', 'store')->name('pages.store')->middleware('permission:create pages');
        Route::get('/{page}/edit', 'edit')->name('pages.edit')->middleware('permission:edit pages');
        Route::put('/{page}', 'update')->name('pages.update')->middleware('permission:edit pages');
        Route::delete('/{page}', 'destroy')->name('pages.destroy')->middleware('permission:delete pages');
    });

    # Menu management
    Route::group(['prefix' => 'menu', 'controller' => MenusController::class], function () {
        Route::get('/', 'index')->name('menu.index')->middleware('permission:view menu');
        Route::get('/create', 'create')->name('menu.create')->middleware('permission:create menu');
        Route::post('/', 'store')->name('menu.store')->middleware('permission:create menu');
        Route::get('/{menu}/edit', 'edit')->name('menu.edit')->middleware('permission:edit menu');
        Route::put('/{menu}', 'update')->name('menu.update')->middleware('permission:edit menu');
        Route::delete('/{menu}', 'destroy')->name('menu.destroy')->middleware('permission:delete menu');
        Route::post('/update-order', 'updateOrder')->name('menu.updateOrder');
    });

    # Menu post kategori
    Route::group(['prefix' => 'categories', 'controller' => PostCategories::class], function () {
        Route::get('/', 'index')->name('categories.index')->middleware('permission:view categories');
        Route::get('/create', 'create')->name('categories.create')->middleware('permission:create categories');
        Route::post('/', 'store')->name('categories.store')->middleware('permission:create categories');
        Route::get('/{id}/edit', 'edit')->name('categories.edit')->middleware('permission:edit categories');
        Route::put('/{id}', 'update')->name('categories.update')->middleware('permission:edit categories');
        Route::delete('/{id}', 'destroy')->name('categories.destroy')->middleware('permission:delete categories');
    });

    # Menu post tags
    Route::group(['prefix' => 'tags', 'controller' => PostTagsController::class], function () {
        Route::get('/', 'index')->name('tags.index')->middleware('permission:view tags');
        Route::get('/create', 'create')->name('tags.create')->middleware('permission:create tags');
        Route::post('/', 'store')->name('tags.store')->middleware('permission:create tags');
        Route::get('/{id}/edit', 'edit')->name('tags.edit')->middleware('permission:edit tags');
        Route::put('/{id}', 'update')->name('tags.update')->middleware('permission:edit tags');
        Route::delete('/{id}', 'destroy')->name('tags.destroy')->middleware('permission:delete tags');
    });

    # Menu post posts
    Route::group(['prefix' => 'posts', 'controller' => PostsController::class], function () {
        Route::get('/', 'index')->name('posts.index')->middleware('permission:view posts');
        Route::get('/create', 'create')->name('posts.create')->middleware('permission:create posts');
        Route::post('/', 'store')->name('posts.store')->middleware('permission:create posts');
        Route::get('/{id}/edit', 'edit')->name('posts.edit')->middleware('permission:edit posts');
        Route::put('/{id}', 'update')->name('posts.update')->middleware('permission:edit posts');
        Route::delete('/{id}', 'destroy')->name('posts.destroy')->middleware('permission:delete posts');
    });

    # Menu albumn
    Route::group(['prefix' => 'album', 'controller' => AlbumController::class], function () {
        Route::get('/', 'index')->name('album.index')->middleware('permission:view album');
        Route::get('/create', 'create')->name('album.create')->middleware('permission:create album');
        Route::post('/', 'store')->name('album.store')->middleware('permission:create album');
        Route::get('/{id}/edit', 'edit')->name('album.edit')->middleware('permission:edit album');
        Route::put('/{id}', 'update')->name('album.update')->middleware('permission:edit album');
        Route::delete('/{id}', 'destroy')->name('album.destroy')->middleware('permission:delete album');
        Route::delete('/{albumId}/photos/{photoId}', 'destroyPhoto')->name('album.photo.destroy');
    });

    # Menu video
    Route::group(['prefix' => 'video', 'controller' => VideosController::class], function () {
        Route::get('/', 'index')->name('video.index')->middleware('permission:view video');
        Route::get('/create', 'create')->name('video.create')->middleware('permission:create video');
        Route::post('/', 'store')->name('video.store')->middleware('permission:create video');
        Route::get('/{id}/edit', 'edit')->name('video.edit')->middleware('permission:edit video');
        Route::put('/{id}', 'update')->name('video.update')->middleware('permission:edit video');
        Route::delete('/{id}', 'destroy')->name('video.destroy')->middleware('permission:delete video');
    });

    # Menu information
    Route::group(['prefix' => 'information', 'controller' => InformationController::class], function () {
        Route::get('/', 'index')->name('information.index')->middleware('permission:view information');
        Route::get('/create', 'create')->name('information.create')->middleware('permission:create information');
        Route::post('/', 'store')->name('information.store')->middleware('permission:create information');
        Route::get('/{id}/edit', 'edit')->name('information.edit')->middleware('permission:edit information');
        Route::put('/{id}', 'update')->name('information.update')->middleware('permission:edit information');
        Route::delete('/{id}', 'destroy')->name('information.destroy')->middleware('permission:delete information');
    });

    # Menu Ads
    Route::group(['prefix' => 'ads', 'controller' => AdsController::class], function () {
        Route::get('/', 'index')->name('ads.index')->middleware('permission:view ads');
        Route::get('/create', 'create')->name('ads.create')->middleware('permission:create ads');
        Route::post('/{id}/toggle', 'toggle')->name('ads.toggle')->middleware('permission:create ads');
        Route::post('/', 'store')->name('ads.store')->middleware('permission:create ads');
        Route::get('/{id}/edit', 'edit')->name('ads.edit')->middleware('permission:edit ads');
        Route::put('/{id}', 'update')->name('ads.update')->middleware('permission:edit ads');
        Route::delete('/{id}', 'destroy')->name('ads.destroy')->middleware('permission:delete ads');
    });


    # Menu Domain Share
    Route::group(['prefix' => 'domain-share', 'controller' => DomainShareController::class], function () {
        Route::get('/', 'index')->name('domain-share.index')->middleware('permission:view domain-share');
        Route::get('/create', 'create')->name('domain-share.create')->middleware('permission:create domain-share');
        Route::post('/', 'store')->name('domain-share.store')->middleware('permission:create domain-share');
        Route::get('/{id}/edit', 'edit')->name('domain-share.edit')->middleware('permission:edit domain-share');
        Route::put('/{id}', 'update')->name('domain-share.update')->middleware('permission:edit domain-share');
        Route::delete('/{id}', 'destroy')->name('domain-share.destroy')->middleware('permission:delete domain-share');
    });

    # Menu RSS Yahoo AI
    Route::group(['prefix' => 'rss-yahoo', 'controller' => RssController::class], function () {
        Route::get('/', 'yahooIndex')->name('rss.yahoo.index');
        Route::post('/fetch', 'fetchYahoo')->name('rss.fetch-yahoo');
    });

    # Artikel Referensi (scrape + AI generate)
    Route::group(['prefix' => 'ref-articles', 'controller' => RefArticleController::class], function () {
        Route::get('/', 'index')->name('ref-articles.index');
        Route::get('/{refArticle}', 'show')->name('ref-articles.show');
        Route::post('/scrape', 'scrape')->name('ref-articles.scrape');
        Route::post('/generate-all', 'generateAll')->name('ref-articles.generate-all');
        Route::post('/{refArticle}/generate', 'generateOne')->name('ref-articles.generate');
        Route::post('/{refArticle}/retry', 'retry')->name('ref-articles.retry');
        Route::delete('/{refArticle}', 'destroy')->name('ref-articles.destroy');
    });

});

#maintenance
Route::get('/maintenance', function () {
    return view('pages.client.maintenance');
});

#client
Route::group(['prefix' => '/', 'controller' => InterfaceController::class, 'middleware' => IsActive::class], function () {
    #dinamis
    Route::get('/', 'beranda')->name('beranda');
    Route::get('/search', 'search')->name('search');
    Route::get('/videos', 'videos')->name('videos');
    Route::get('/posts', 'posts')->name('posts');
    Route::get('/banners', 'banners')->name('banners');
    Route::get('/albums', 'albums')->name('albums');
    Route::get('/info', 'info')->name('info');

    #statis
    Route::get('/author/{slug}', 'author')->name('author');
    Route::get('/tag/{slug}', 'tag')->name('tag');
    Route::get('/video/{slug}', 'video_detail')->name('video_detail');
    Route::get('/banner/{slug}', 'banner_detail')->name('banner_detail');
    Route::get('/info/{slug}', 'info_detail')->name('info_detail');
    Route::get('/page/{slug}', 'page_detail')->name('page_detail');

    #sensitif route
    Route::get('/{slug}', 'category')->name('category');
    Route::get('/{category}/{post}', 'post_detail')->name('post_detail');
});

