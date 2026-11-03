@extends('user.layout')
@section('title')
 {{__('Reset Password')}}
@stop
@section('meta-data')
<meta property="og:type" content="website"/>
<meta property="og:url" content="{{__('Chiremba Express')}}"/>
<meta property="og:title" content="{{__('Chiremba Express')}}"/>
<meta property="og:image" content="{{asset('image_web/').'/'.$setting->favicon}}"/>
<meta property="og:image:width" content="250px"/>
<meta property="og:image:height" content="250px"/>
<meta property="og:site_name" content="{{__('Chiremba Express')}}"/>
<meta property="og:description" content="{{__('meta_description')}}"/>
<meta property="og:keyword" content="{{__('Meta Keyword')}}"/>
<link rel="shortcut icon" href="{{asset('image_web/').'/'.$setting->favicon}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
@stop
@section('content')
<section class="page-title-two">
            <div class="title-box centred bg-color-2">
                <div class="pattern-layer">
                    <div class="pattern-1" style="background-image: url('{{asset('front_pro/assets/images/shape/shape-70.png')}}');"></div>
                    <div class="pattern-2" style="background-image: url('{{asset('front_pro/assets/images/shape/shape-71.png')}}');"></div>
                </div>
                <div class="auto-container">
                    <div class="title">
                        <h1>{{__('Reset Password')}}</h1>
                    </div>
                </div>
            </div>
            <div class="lower-content">
                <div class="auto-container">
                    <ul class="bread-crumb clearfix">
                        <li><a href="{{url('/')}}">{{__('Home')}}</a></li>
                        <li>{{__('Reset Password')}}</li>
                    </ul>
                </div>
            </div>
</section>
<section class="registration-section bg-color-3">
            <div class="pattern">
                <div class="pattern-1" style="background-image: url('{{asset('front_pro/assets/images/shape/shape-85.png')}}');"></div>
                <div class="pattern-2" style="background-image: url('{{asset('front_pro/assets/images/shape/shape-86.png')}}');"></div>
            </div>
            <div class="auto-container">
                <div class="inner-box">
                    <div class="content-box">
                        <div class="title-box">
                            <h3>{{__('Reset Password')}}</h3>
                           
                        </div>
                        <div class="inner">
                             @if(Session::has('message'))
                              <div class="col-sm-12">
                                 <div class="alert  {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">{{ Session::get('message') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                    </button>
                                 </div>
                              </div>
                              @endif
                              @if(!isset($msg))
                            <form action="{{url('resetnewpwd')}}" method="post" class="registration-form">
                                 {{csrf_field()}}
                                 <input type="hidden" name="code" value="{{$code}}" />
                                 <input type="hidden" name="id" value="{{$id}}" />
                                 <input type="hidden" name="type" value="{{$type}}" />
                                <div class="row clearfix">
                                    
                                    <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                        <label>{{__("Enter New Password")}}</label>
                                        <input type="password"  name="npwd" id="npwd" placeholder="{{__('Enter New Password')}}" required="">
                                    </div>
                                     <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                        <label>{{__("Enter Re Enter New Password")}}</label>
                                        <input type="password"  name="rpwd" id="rpwd" placeholder="{{__('Enter Re Enter New Password')}}" required="">
                                    </div>
                                   
                                    <div class="col-lg-12 col-md-12 col-sm-12 form-group message-btn">
                                        <button type="submit" class="theme-btn-one">{{__('Reset Password')}}<i class="icon-Arrow-Right"></i></button>
                                    </div>
                                </div>
                            </form>
                             @else
                              <h3>{{$msg}}</h3>
                               
                              @endif
                           
                        </div>
                    </div>
                </div>
            </div>
        </section>
       <section class="agent-section" style="background: aliceblue;">
   <div class="auto-container">
      <div class="inner-container bg-color-2">
         <div class="row clearfix">
            <div class="col-lg-6 col-md-12 col-sm-12 left-column">
               <div class="content_block_3">
                  <div class="content-box">
                     <h3>{{__('Emergency call')}}</h3>
                     <div class="support-box">
                        <div class="icon-box"><i class="fas fa-phone"></i></div>
                        <span>{{__('Telephone')}}</span>
                        <h3><a href="tel:{{$setting->phone}}">{{$setting->phone}}</a></h3>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12 right-column">
               <div class="content_block_4">
                  <div class="content-box">
                     <h3>{{__('Sign up for Newsletter today')}}</h3>
                     <form action="#" method="post" class="subscribe-form">
                        <div class="form-group">
                           <input type="email" name="email" id="emailnews" placeholder="{{__('Your email')}}" required="">
                           <button type="button" onclick="addnewsletter('emailnews')" class="theme-btn-one">{{__('Submit now')}}<i class="icon-Arrow-Right"></i></button>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</section>
@stop
@section('footer')
@stop