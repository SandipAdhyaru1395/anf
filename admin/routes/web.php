<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\apps\EcommerceDashboard;
use App\Http\Controllers\apps\ProductController;
use App\Http\Controllers\apps\SupplierController;
use App\Http\Controllers\apps\BrandController;
use App\Http\Controllers\apps\CategoryController;
use App\Http\Controllers\apps\CustomerController;
use App\Http\Controllers\apps\OrderController;
use App\Http\Controllers\apps\BranchController;
use App\Http\Controllers\apps\PurchaseController;
use App\Http\Controllers\apps\UserController;
use App\Http\Controllers\apps\SettingController;
use App\Http\Controllers\apps\RoleController;
use App\Http\Controllers\apps\QuantityAdjustmentController;
use App\Http\Controllers\apps\ReportController;
use App\Http\Controllers\pages\UserProfile;
use App\Http\Controllers\pages\UserTeams;
use App\Http\Controllers\pages\UserProjects;
use App\Http\Controllers\pages\UserConnections;
use App\Http\Controllers\Auth\LoginController as AuthLoginController;
use App\Http\Controllers\Auth\RegisterController as AuthRegisterController;

// Current Working Routes

//dashboard
Route::middleware(['auth', 'sidebar'])->group(function () {

    Route::middleware('permission:dashboard.read')->group(function () {
        Route::get('/dashboard', [EcommerceDashboard::class, 'index'])->name('dashboard.read');
    });

    // Category

    Route::middleware('permission:category.read')->group(function () {
        Route::get('/category', [CategoryController::class, 'index'])->name('category.list');
        Route::get('/category/list/ajax', [CategoryController::class, 'ajaxList'])->name('category.list.ajax');
        Route::get('/category/edit/{id}', [CategoryController::class, 'edit'])->name('category.edit');
        
    });

    Route::middleware('permission:category.create')->group(function () {
        Route::get('/category/add', [CategoryController::class, 'add'])->name('category.add');
        Route::post('/category/create', [CategoryController::class, 'create'])->name('category.create');
    });

    Route::middleware('permission:category.write')->group(function () {
        Route::post('/category/update', [CategoryController::class, 'update'])->name('category.update');
        Route::get('/category/delete/{id}', [CategoryController::class, 'delete'])->name('category.delete');
        Route::post('/category/delete-multiple', [CategoryController::class, 'deleteMultiple']);
    });


    // Brand
    Route::middleware('permission:brand.read')->group(function () {
        Route::get('/brand', [BrandController::class, 'index'])->name('brand.list');
       Route::get('/brand/edit/{id}', [BrandController::class, 'edit'])->name('brand.edit');
        Route::get('/brand/list/ajax', [BrandController::class, 'ajaxList'])->name('brand.list.ajax');
    });

    Route::middleware('permission:brand.write')->group(function () {
         Route::post('/brand/update', [BrandController::class, 'update'])->name('brand.update');
         Route::get('/brand/delete/{id}', [BrandController::class, 'delete'])->name('brand.delete');
        Route::post('/brand/delete-multiple', [BrandController::class, 'deleteMultiple']);
    });

    Route::middleware('permission:brand.create')->group(function () {
        Route::get('/brand/add', [BrandController::class, 'add'])->name('brand.add');
         Route::post('/brand/create', [BrandController::class, 'create'])->name('brand.create');
    });

    // Product
    Route::middleware('permission:product.read')->group(function () {
        Route::get('/product', [ProductController::class, 'index'])->name('product.list');
        Route::get('/product/list/ajax', [ProductController::class, 'ajaxList'])->name('product.list.ajax');
        Route::get('/product/edit/{id}', [ProductController::class, 'edit'])->name('product.edit');
        Route::get('/product/search/ajax', [ProductController::class, 'searchAjax'])->name('product.search.ajax');
        Route::post('/check-sku', [ProductController::class, 'checkSku'])->name('product.checkSku');
        Route::post('/check-unit-sku', [ProductController::class, 'checkUnitSku'])->name('product.checkUnitSku');
    });
    
    Route::middleware('permission:product.create')->group(function () {
        Route::get('/product/add', [ProductController::class, 'add'])->name('product.add');
        Route::post('/product/create', [ProductController::class, 'create'])->name('product.create');
        Route::post('/product/import', [ProductController::class, 'import'])->name('product.import');
        Route::get('/product/import/sample', [ProductController::class, 'downloadSample'])->name('product.import.sample');
    });

    Route::middleware('permission:product.write')->group(function () {
        Route::post('/product/update', [ProductController::class, 'update'])->name('product.update');
        Route::get('/product/delete/{id}', [ProductController::class, 'delete'])->name('product.delete');
        Route::post('/product/delete-multiple', [ProductController::class, 'deleteMultiple']);
    });

    // Quantity Adjustments
    Route::middleware('permission:quantity-adjustment.read')->group(function () {
        Route::get('/quantity-adjustment', [QuantityAdjustmentController::class, 'index'])->name('quantity_adjustment.list');
        Route::get('/quantity-adjustment/list/ajax', [QuantityAdjustmentController::class, 'ajaxList'])->name('quantity_adjustment.list.ajax');
        Route::get('/quantity-adjustment/edit/{id}', [QuantityAdjustmentController::class, 'edit'])->name('quantity_adjustment.edit');
        Route::get('/quantity-adjustment/search/ajax', [QuantityAdjustmentController::class, 'searchAjax'])->name('quantity_adjustment.search.ajax');
        Route::get('/quantity-adjustment/show/ajax/{id}', [QuantityAdjustmentController::class, 'showAjax'])->name('quantity_adjustment.show.ajax');
    });

    Route::middleware('permission:quantity-adjustment.create')->group(function () {
        Route::get('/quantity-adjustment/add', [QuantityAdjustmentController::class, 'add'])->name('quantity_adjustment.add');
        Route::post('/quantity-adjustment/create', [QuantityAdjustmentController::class, 'create'])->name('quantity_adjustment.create');
    });

    Route::middleware('permission:quantity-adjustment.write')->group(function () {
        Route::post('/quantity-adjustment/update', [QuantityAdjustmentController::class, 'update'])->name('quantity_adjustment.update');
        Route::get('/quantity-adjustment/delete/{id}', [QuantityAdjustmentController::class, 'delete'])->name('quantity_adjustment.delete');
        Route::post('/quantity-adjustment/delete-multiple', [QuantityAdjustmentController::class, 'deleteMultiple']);
    });

    // Supplier
    Route::middleware('permission:supplier.read')->group(function () {
        Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.list');
        Route::get('/supplier/list/ajax', [SupplierController::class, 'ajaxList'])->name('supplier.list.ajax');
        Route::get('/supplier/edit/{id}', [SupplierController::class, 'edit'])->name('supplier.edit');
        Route::post('/supplier/check-email', [SupplierController::class, 'checkEmail'])->name('supplier.checkEmail');
        Route::post('/supplier/check-phone', [SupplierController::class, 'checkPhone'])->name('supplier.checkPhone');
    });
    
    Route::middleware('permission:supplier.create')->group(function () {
        Route::get('/supplier/add', [SupplierController::class, 'add'])->name('supplier.add');
        Route::post('/supplier/create', [SupplierController::class, 'create'])->name('supplier.create');
    });

    Route::middleware('permission:supplier.write')->group(function () {
        Route::post('/supplier/update', [SupplierController::class, 'update'])->name('supplier.update');
        Route::get('/supplier/delete/{id}', [SupplierController::class, 'delete'])->name('supplier.delete');
        Route::post('/supplier/delete-multiple', [SupplierController::class, 'deleteMultiple']);
    });

    //Order
    Route::middleware('permission:order.read')->group(function () {
        Route::get('/order', [OrderController::class, 'index'])->name('order.list');
        Route::get('/order/details', [OrderController::class, 'getOrderDetails'])->name('order-details');
        Route::get('/order/edit/{id}', [OrderController::class, 'edit'])->name('order.edit');
        Route::get('/order/list/ajax', [OrderController::class, 'ajaxList'])->name('order.list.ajax');
        Route::get('/order/show/ajax/{id}', [OrderController::class, 'showAjax'])->name('order.show.ajax');
        Route::get('/order/invoice/{id}', [OrderController::class, 'showInvoice'])->name('order.invoice');
        Route::get('/order/invoice/{id}/pdf', [OrderController::class, 'generateInvoicePdf'])->name('order.invoice.pdf');
        Route::post('/order/invoice/{id}/email', [OrderController::class, 'sendInvoiceEmail'])->name('order.invoice.email');
        Route::get('/order/statistics', [OrderController::class, 'getStatistics'])->name('order.statistics');
        Route::get('/order/items/ajax', [OrderController::class, 'itemsAjax'])->name('order.items.ajax');
        Route::post('/order/update', [OrderController::class, 'update'])->name('order.update');
        Route::get('/order/customer/{id}/branches', [OrderController::class, 'getCustomerBranches'])->name('order.customer.branches');
    });

    Route::middleware('permission:order.create')->group(function () {
        Route::get('/order/add', [OrderController::class, 'add'])->name('order.add');
        Route::post('/order/create', [OrderController::class, 'create'])->name('order.create');
        Route::post('/order/item/create', [OrderController::class, 'createItem'])->name('order.item.create');
        Route::get('/order/credit-note/add/{id}', [OrderController::class, 'creditNoteAdd'])->name('credit-note.add');
        Route::post('/order/credit-note/store', [OrderController::class, 'creditNoteStore'])->name('credit-note.store');
    });
    
    // Order Items CRUD
    Route::middleware('permission:order.write')->group(function () {
        Route::post('/order/item/update', [OrderController::class, 'updateItem'])->name('order.item.update');
        Route::get('/order/delete/{id}', [OrderController::class, 'delete'])->name('order.delete');
        Route::delete('/order/item/delete/{id}', [OrderController::class, 'deleteItem'])->name('order.item.delete');
        Route::post('/order/payment/add', [OrderController::class, 'addPayment'])->name('order.payment.add');
        Route::get('/order/payments/{orderId}', [OrderController::class, 'getPayments'])->name('order.payments');
        Route::delete('/order/payment/delete/{paymentId}', [OrderController::class, 'deletePayment'])->name('order.payment.delete');
        Route::post('/order/delete-multiple', [OrderController::class, 'deleteMultiple']);
    });


    //Customer
    Route::middleware('permission:customer.read')->group(function () {
        Route::get('/customer', [CustomerController::class, 'index'])->name('customer.list');
        Route::get('/customer/list/ajax', [CustomerController::class, 'ajaxList'])->name('customer.list.ajax');
        Route::get('/customer/{id}/orders/ajax', [CustomerController::class, 'ordersAjax'])->name('customer.orders.ajax');
        Route::get('/customer/{id?}/overview', [CustomerController::class, 'overview'])->name('customer.overview');
        Route::get('/customer/{id?}/security', [CustomerController::class, 'security'])->name('customer.security');
        Route::get('/customer/{id?}/branches', [CustomerController::class, 'branches'])->name('customer.branches');
        Route::get('/customer/{id?}/notifications', [CustomerController::class, 'notifications'])->name('customer.notifications');
        Route::get('/customer/branch/edit', [BranchController::class, 'edit'])->name('customer.branch.edit');
    });

    Route::middleware('permission:customer.write')->group(function () {
        Route::delete('/customer/{id}', [CustomerController::class, 'destroy'])->name('customer.destroy');
        Route::post('/customer/update/password', [CustomerController::class, 'updatePassword'])->name('customer.update-password');
        Route::post('/customer/update', [CustomerController::class, 'update'])->name('customer.update');
        Route::post('/customer/branch/update', [BranchController::class, 'update'])->name('customer.branch.update');
        Route::get('/customer/branch/{branch}/delete', [BranchController::class, 'destroy'])->name('customer.branch.delete');
        Route::post('/customer/delete-multiple', [CustomerController::class, 'deleteMultiple']);
    });
    
    Route::middleware('permission:customer.create')->group(function () {
        Route::post('/customer/store', [CustomerController::class, 'store'])->name('customer.store');
        Route::post('/customer/branch/store', [BranchController::class, 'store'])->name('customer.branch.store');
    });

    // Purchase
    Route::middleware('permission:purchase.read')->group(function () {
        Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase.list');
        Route::get('/purchase/list/ajax', [PurchaseController::class, 'ajaxList'])->name('purchase.list.ajax');
        Route::get('/purchase/edit/{id}', [PurchaseController::class, 'edit'])->name('purchase.edit');
        Route::get('/purchase/search/ajax', [PurchaseController::class, 'searchAjax'])->name('purchase.search.ajax');
        Route::get('/purchase/show/ajax/{id}', [PurchaseController::class, 'showAjax'])->name('purchase.show.ajax');
    });
    
    Route::middleware('permission:purchase.create')->group(function () {
        Route::get('/purchase/add', [PurchaseController::class, 'add'])->name('purchase.add');
        Route::post('/purchase/create', [PurchaseController::class, 'create'])->name('purchase.create');
    });
    
    Route::middleware('permission:purchase.write')->group(function () {
        Route::post('/purchase/update', [PurchaseController::class, 'update'])->name('purchase.update');
        Route::get('/purchase/delete/{id}', [PurchaseController::class, 'delete'])->name('purchase.delete');
        Route::post('/purchase/delete-multiple', [PurchaseController::class, 'deleteMultiple']);
    });


    // Users
    Route::middleware('permission:user.read')->group(function () {

        Route::get('/user', [UserController::class, 'index'])->name('user.list');
        Route::get('/user/list/ajax', [UserController::class, 'ajaxUserAll'])->name('user.list.ajax');
        
        Route::get('/user/ajax/show', [UserController::class, 'ajaxShow'])->name('user-ajax.show');

        Route::get('/user/view/account/{id}', [UserController::class, 'viewAccount'])->name('user-view-account.read');
        Route::get('/user/view/security/{id}', [UserController::class, 'viewSecurity'])->name('user-view-security.read');
        Route::get('/user/view/notifications/{id}', [UserController::class, 'viewNotifications'])->name('user-view-notifications.read');

        Route::get('/profile-user', [UserProfile::class, 'index'])->name('profile-user.read');
        Route::get('/profile-teams', [UserTeams::class, 'index'])->name('profile-teams.read');
        Route::get('/profile-projects', [UserProjects::class, 'index'])->name('profile-projects.read');
        Route::get('/profile-connections', [UserConnections::class, 'index'])->name('profile-connections.read');
    });

    Route::middleware('permission:user.write')->group(function () {
        Route::post('/user/update', [UserController::class, 'update'])->name('user.update');
        Route::post('/user/update/password', [UserController::class, 'updatePassword'])->name('user.update-password');
        Route::get('user/change/status/{id}', [UserController::class, 'changeStatus'])->name('user.change-status');
        Route::get('/user/delete/{id}', [UserController::class, 'delete'])->name('user.delete');
        Route::post('/user/delete-multiple', [UserController::class, 'deleteMultiple']);
    });

    Route::middleware('permission:user.create')->group(function () {
        Route::post('/user/create', [UserController::class, 'create'])->name('user.create');
    });

    // Roles & Permissions
    Route::middleware('permission:role.read')->group(function () {
        Route::get('/role', [RoleController::class, 'index'])->name('role.list');
        Route::get('/role/show', [RoleController::class, 'show'])->name('role.show');
        Route::get('/user/ajax/list/with/roles', [UserController::class, 'ajaxUserListWithRoles'])->name('user.list.ajax.with.roles');
    });

    Route::middleware('permission:role.create')->group(function () {
        Route::post('/role/store', [RoleController::class, 'store'])->name('role.store');
    });

    Route::middleware('permission:role.write')->group(function () {
        Route::post('/role/update', [RoleController::class, 'update'])->name('role.update');
    });

    

    // Report
    Route::middleware('permission:report.read')->group(function () {
        Route::get('/report', [ReportController::class, 'index'])->name('report.index');
        Route::get('/report/sales', [ReportController::class, 'salesReport'])->name('report.sales');
        Route::get('/report/sales/ajax', [ReportController::class, 'salesReportAjax'])->name('report.sales.ajax');
        Route::get('/report/daily-sales', [ReportController::class, 'dailySalesReport'])->name('report.daily-sales');
        Route::get('/report/daily-sales/ajax', [ReportController::class, 'dailySalesReportAjax'])->name('report.daily-sales.ajax');
        Route::get('/report/monthly-sales', [ReportController::class, 'monthlySalesReport'])->name('report.monthly-sales');
        Route::get('/report/monthly-sales/ajax', [ReportController::class, 'monthlySalesReportAjax'])->name('report.monthly-sales.ajax');
        Route::get('/report/net-vat', [ReportController::class, 'netVatReport'])->name('report.net-vat');
        Route::get('/report/net-vat/ajax', [ReportController::class, 'netVatReportAjax'])->name('report.net-vat.ajax');
    });

    Route::middleware('permission:settings.read')->group(function () {
        Route::get('/settings', [SettingController::class, 'viewGeneralSettings'])->name('settings.general');
        Route::get('/settings/banner', [SettingController::class, 'viewBannerSettings'])->name('settings.banner');
        Route::get('/settings/maintenance', [SettingController::class, 'viewMaintenanceSettings'])->name('settings.maintenance');
        Route::get('/settings/theme', [SettingController::class, 'viewThemeSettings'])->name('settings.theme');
        Route::get('/settings/delivery-method', [SettingController::class, 'viewDeliveryMethod'])->name('settings.deliveryMethod');
        Route::get('/settings/delivery-method/list/ajax', [SettingController::class, 'deliveryMethodListAjax'])->name('settings.deliveryMethod.list.ajax');
        Route::get('/settings/delivery-method/ajax/show', [SettingController::class, 'deliveryMethodShow'])->name('settings.deliveryMethod.ajax.show');
        // VAT Methods (read)
        Route::get('/settings/vat-method', [SettingController::class, 'viewVatMethod'])->name('settings.vatMethod');
        Route::get('/settings/vat-method/list/ajax', [SettingController::class, 'vatMethodListAjax'])->name('settings.vatMethod.list.ajax');
        Route::get('/settings/vat-method/ajax/show', [SettingController::class, 'vatMethodShow'])->name('settings.vatMethod.ajax.show');
        Route::get('/settings/unit', [SettingController::class, 'viewUnit'])->name('settings.unit');
        Route::get('/settings/unit/list/ajax', [SettingController::class, 'unitListAjax'])->name('settings.unit.list.ajax');
        Route::get('/settings/unit/ajax/show', [SettingController::class, 'unitShow'])->name('settings.unit.ajax.show');
    });

    Route::middleware('permission:settings.update')->group(function () {
        Route::post('/settings/general/update', [SettingController::class, 'updateGeneralSettings'])->name('settings.general.update');
        Route::post('/settings/banner/update', [SettingController::class, 'updateBannerSettings'])->name('settings.banner.update');
        Route::post('/settings/maintenance/update', [SettingController::class, 'updateMaintenanceSettings'])->name('settings.maintenance.update');
        Route::post('/settings/truncate', [SettingController::class, 'truncateData'])->name('settings.truncate');
        Route::post('/settings/theme/update', [SettingController::class, 'updateThemeSettings'])->name('settings.theme.update');
        Route::post('/settings/delivery-method/store', [SettingController::class, 'deliveryMethodStore'])->name('settings.deliveryMethod.store');
        Route::post('/settings/delivery-method/update', [SettingController::class, 'deliveryMethodUpdate'])->name('settings.deliveryMethod.update');
        Route::get('/settings/delivery-method/delete/{id}', [SettingController::class, 'deliveryMethodDelete'])->name('settings.deliveryMethod.delete');
        // VAT Methods (write)
        Route::post('/settings/vat-method/store', [SettingController::class, 'vatMethodStore'])->name('settings.vatMethod.store');
        Route::post('/settings/vat-method/update', [SettingController::class, 'vatMethodUpdate'])->name('settings.vatMethod.update');
        Route::get('/settings/vat-method/delete/{id}', [SettingController::class, 'vatMethodDelete'])->name('settings.vatMethod.delete');
        Route::post('/settings/unit/store', [SettingController::class, 'unitStore'])->name('settings.unit.store');
        Route::post('/settings/unit/update', [SettingController::class, 'unitUpdate'])->name('settings.unit.update');
        Route::get('/settings/unit/delete/{id}', [SettingController::class, 'unitDelete'])->name('settings.unit.delete');
    });
});

Route::get('/', [AuthLoginController::class, 'show']);
// Auth routes (blank layout, custom blades)
Route::post('/logout', [AuthLoginController::class, 'logout'])->name('logout');
Route::get('/login', [AuthLoginController::class, 'show'])->name('login');
Route::post('/login', [AuthLoginController::class, 'login'])->name('login.post');

Route::get('/register', [AuthRegisterController::class, 'show'])->name('register');
Route::post('/register', [AuthRegisterController::class, 'register'])->name('register.post');

////////////// End working routes

