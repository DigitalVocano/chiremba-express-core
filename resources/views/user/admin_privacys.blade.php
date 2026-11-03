@extends('user.layout')
@section('title')
Privecy | Admin Dashboard
@stop
@section('meta-data')
@stop
@section('content')
<style>
    td.dataTables_empty {
    font-size: medium;
    font-weight: 600;
}
</style>
<div class="main-content">
  <div class="page-content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              @if(Session::has('message')) {{-- Converted to Alpine.js for consistency --}}
                 <div x-data="{ showMessage: true }"
                      x-show="showMessage"
                      x-transition:enter="transition ease-out duration-300"
                      x-transition:enter-start="opacity-0 transform -translate-y-4"
                      x-transition:enter-end="opacity-100 transform translate-y-0"
                      x-transition:leave="transition ease-in duration-200"
                      x-transition:leave-start="opacity-100 transform translate-y-0"
                      x-transition:leave-end="opacity-0 transform -translate-y-4"
                      x-init="setTimeout(() => showMessage = false, 5000)"
                      class="mb-6 rounded-lg p-4 {{ Session::get('alert-class', 'bg-blue-100 text-blue-700') == 'alert-success' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' }}" role="alert">
                     <p class="font-medium">{{ Session::get('message') }}</p>
                     <button type="button" @click="showMessage = false" class="text-xl leading-none font-semibold opacity-75 hover:opacity-100">&times;</button>
                 </div>
              @endif
              <h4>Privecy</h4>
                <div class="content mt-3">
                  <div class="animated">
                    <div class="col-sm-10">
                      <div class="modal-content">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Privecy</h5>
                          </div>
                          <div class="modal-body">
                            <form action="{{url('admin/edit_privecy')}}" method="post" enctype="multipart/form-data">
                              {{csrf_field()}} 
                              <div class="form-group">
                                <label>Privecy</label>
                                
                                <input type="hidden" class="form-control" id="id" name="id" required="" value="{{isset($data->id)?$data->id:0}}">

                                <textarea class="form-control" name="privecy">{{isset($data->privecy)?$data->privecy:''}}</textarea>
                                
                              </div>
                              <button name="update_about" type="submit" class="btn btn-md btn-success ">submit</button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>          
            </div>
          </div>
        </div> 
      </div> 
    </div> 
  </div>
</div>

<script src="{{asset('js/vendor/jquery-2.1.4.min.js')}}"></script>
 <script src="{{asset('ckeditor/ckeditor.js')}}"></script>
  <script type="text/javascript">
    $(document).ready(function () 
      {
          CKEDITOR.replace('privecy');
      });
  </script>
@stop
@section('footer')
@stop
