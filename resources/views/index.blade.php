@include('header')
<div class="container">
    <div class="d-flex justify-content-end mt-3">
        <div class="dropdown">
            <a  title="Settings" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"   style="color:#157347;">
                <i class="fa fa-cog" aria-hidden="true"></i>
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#threshold">Discrepancy Threshold</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#format_setting">Format Setting</a></li>
            </ul>
          </div>

        <!-- Modal Threshold-->
        <div class="modal fade" id="threshold" tabindex="-1" aria-labelledby="thresholdLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="thresholdLabel">Discrepancy Threshold</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{!! route('threshold') !!}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col">
                                    <label for="value_from" class="form-label">Value from</label>
                                    <input type="text" name="value_from" value="{{$threshold->value_from}}" class="form-control" id="value_from">
                                </div>
                                <div class="col pull-right">
                                    <label for="value_to" class="form-label">Value to</label>
                                    <input type="text" name="value_to" value="{{$threshold->value_to}}" class="form-control" id="value_to">
                                </div>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Format-->
        <div class="modal fade" id="format_setting" tabindex="-1" aria-labelledby="format_settingLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="format_settingLabel">Format Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{!! route('format') !!}" method="POST">
                            @csrf
                           
                            <div class="col">
                                <label for="merchant_code" class="form-label">Merchant Code length</label>
                                <input type="text" name="merchant_code" value="{{$format->merchant_code_length}}" class="form-control" id="merchant_code">
                            </div>
        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container text-center mb-5 mt-0">
        <div class="row">
            <div class="col-md-12">
                <h1>File Checker</h1>
            </div>
        </div>
    </div>
     <!--include messages -->
     @include('message')

    <form id="multi-file-upload-ajax" class="class-form" method="POST" action="javascript:void(0)" accept-charset="utf-8"
        enctype="multipart/form-data">
        @csrf
        <section>
            <div class="d-flex justify-content-center mb-3">
                <div class="file-drop-area">
                    <span class="fake-btn"><i class='fa fa-file-import'></i> Choose files</span>
                    <span class="file-msg">or drag and drop files here</span>
                    <input id="multiplefileupload" class="file-input" name="files[]" type="file"
                        accept=".CSV,.csv,.txt" required multiple>
                </div>
            </div>
            <div class="d-flex justify-content-center mb-3">
                <button type="submit" class="btn btn-success btn-validate"><i class="fas fa-check-circle"></i> Validate
                    the File</button>
            </div>
        </section>

    </form>
    {{-- <div class="row mb-3">
        <div class="col">
            <div>
                <label for="textarea1" class="form-label">Success Filename:</label>
                <textarea class="form-control" id="textarea1" rows="3" readonly></textarea>
            </div>
        </div>
        <div class="col pull-right">
            <div>
                <label for="textarea2" class="form-label">Error Filename:</label>
                <textarea class="form-control" id="textarea2" rows="3" readonly></textarea>
            </div>
        </div>
    </div> --}}

    {{-- <div id="alert-tally" class="alert alert-warning alert-dismissible fade show" role="alert" style="display:none;">
        <strong>Tally!</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <div id="alert-nottally" class="alert alert-danger alert-dismissible fade show" role="alert" style="display:none;">
        <strong>Not Tally!</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div> --}}

{{-- 
    <div class="d-flex justify-content-center">
    <div id="loading" class="spinner-border text-success" role="status" style="display:none;">
        <span class="sr-only">Loading...</span>
      </div>
    </div> --}}

    <div class="d-flex justify-content-center">
        <div id="loading" class="" style="display:none;">
            <div class="lds-facebook"><div></div><div></div><div></div></div>
          </div>
        </div>
 
    

    {{-- Table --}}

    {{-- <div class="tbl-format" id="format" style="display: none;">
        <div class="table-responsive">
            <table id="tbl_format" class="table" style="width:100%">
                <thead>
                    <tr>
                        <th>Error Type</th>
                        <th>Filename</th>
                        <th>Error Description</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div> --}}

        <div class="tbl-validation" id="validation" style="display: none;">
        <div class="table-responsive">
            <table id="tbl_validation" class="table nowrap table-striped table-bordered" style="width:100%;">
                <thead>
                    <tr>
                        <th>Error Type</th>
                        <th>Filename</th>
                        <th>Merchant Code</th>
                        <th>Transaction Date</th>
                        <th>Transaction No.</th>
                        <th>Terminal No.</th>
                        <th>Result</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>
@include('footer')
