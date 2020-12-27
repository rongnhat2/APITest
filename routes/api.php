<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Use App\Article;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// login
Route::post('login', 'CustomerController@login')->name('customer.login');
// signup
Route::post('register', 'CustomerController@register')->name('customer.register');
// get_verify_code
Route::post('get_verify_code', 'CustomerController@getVerifyCode')->name('customer.get_verify_code');
// change_password
Route::post('change_password', 'CustomerController@updatePassword')->name('customer.change_password');
// check_verify_code
Route::post('check_verify_code', 'CustomerController@checkVerifyCode')->name('customer.check_verify_code');
// get_user_info
Route::post('get_user_info', 'CustomerController@getInfo')->name('customer.get_user_info');
// change_info_after_signup
Route::post('create_info', 'CustomerController@createInfo')->name('customer.create_info');
// set_user_info
Route::post('set_user_info', 'CustomerController@updateInfo')->name('customer.set_user_info');
// logout
Route::post('logout', 'CustomerController@logout')->name('customer.logout');
// set_block
Route::post('list_block', 'CustomerController@getListBlock')->name('customer.list_block');
// set_block
Route::post('set_block', 'CustomerController@block')->name('customer.block');
// set_unblock
Route::post('set_unblock', 'CustomerController@unblock')->name('customer.unblock');
// get_push_settings
Route::post('get_setting', 'CustomerController@getSetting')->name('customer.get_setting');
// set_push_settings
Route::post('set_setting', 'CustomerController@setSetting')->name('customer.set_setting');


// get_list_posts
Route::post('get_newsfeed', 'PostController@getNewsfeed')->name('customer.get_newsfeed');
// get_post
Route::post('get_post', 'PostController@get')->name('customer.get_post');
// add_post
Route::post('add_post', 'PostController@create')->name('customer.add_post');
// edit_post
Route::post('edit_post', 'PostController@update')->name('customer.edit_post');
// delete_post
Route::post('delete_post', 'PostController@delete')->name('customer.delete_post');
// report_post
Route::post('report_post', 'PostController@report')->name('customer.report_post');
// list_like				
Route::post('list_like', 'PostController@getListLike')->name('customer.list_like');
// like				
Route::post('like', 'PostController@like')->name('customer.like');
// unlike				
Route::post('unlike', 'PostController@unlike')->name('customer.unlike');


// get_requested_friends
Route::post('get_friend_request_list', 'RelationController@getFriendRequestList')->name('customer.getFriendRequestList');
// get_response_friends
Route::post('get_friend_response_list', 'RelationController@getFriendResponseList')->name('customer.getFriendResponseList');
// set_request_friend
Route::post('add_friend', 'RelationController@createRelation')->name('customer.createRelation');
// set_accept_friend
Route::post('accept_friend', 'RelationController@acceptRelation')->name('customer.acceptRelation');
// get_user_friends
Route::post('get_friend_list', 'RelationController@getFriendList')->name('customer.getFriendList');

// get_comment
Route::post('get_comment', 'CommentController@get')->name('customer.get_comment');
// set_comment
Route::post('set_comment', 'CommentController@create')->name('customer.set_comment');

// search
Route::post('search', 'SearchController@create')->name('customer.search');
// get_saved_search
Route::post('get_saved_search', 'SearchController@get')->name('customer.get_saved_search');
// del_saved_search
Route::post('del_saved_search', 'SearchController@delete')->name('customer.del_saved_search');

// block_message
Route::post('message_block', 'MessageController@setBlock')->name('customer.message_block');
// un_block_message
Route::post('message_un_block', 'MessageController@setUnBlock')->name('customer.message_un_block');
// create_conversation
Route::post('create_conversation', 'MessageController@createConversation')->name('customer.create_conversation');
// get_list_conversation
Route::post('get_list_conversation', 'MessageController@getConversation')->name('customer.get_list_conversation');
// get_conversation
Route::post('get_conversation', 'MessageController@getMessage')->name('customer.get_conversation');
// delete_conversation
Route::post('delete_conversation', 'MessageController@deleteConversation')->name('customer.delete_conversation');
// create_message
Route::post('create_message', 'MessageController@createMessage')->name('customer.create_message');
// set_read_message
Route::post('set_read_message', 'MessageController@updateMessage')->name('customer.set_read_message');
// delete_message
Route::post('delete_message', 'MessageController@deleteMessage')->name('customer.delete_message');


Route::post('checkToken', 'CustomerController@checkToken')->name('customer.checkToken');

Route::post('createTokenClient', 'CustomerController@createTokenClient')->name('customer.createTokenClient');
