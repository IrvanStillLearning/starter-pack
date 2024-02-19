   <script type="text/javascript">

      $('.form_submit').on('submit', function(e){
         e.preventDefault();
         let this_form = this;
   
         Swal.fire({
               title: 'Yakin?',
               text: "Apakah anda yakin akan menyimpan data ini?",
               icon: 'warning',
               showCancelButton: true,
               confirmButtonColor: '#3085d6',
               cancelButtonColor: '#d33',
               confirmButtonText: 'Save Changes'
            }).then((result) => {
               if (result.value) {
                  $("#button_submit").attr('status', "loading");
                  $("#button_submit").attr('disabled', "true");
                  $("#modal_loading").modal('show');
                  $.ajax({
                        url:  $(this).attr('action'),
                        type: $(this).attr('method'),
                        data: $(this).serialize(),
                        success: function(response){
                           setTimeout(function () {  $('#modal_loading').modal('hide'); }, 500);
                           $("#button_submit").removeAttr('status');
                           $("#button_submit").removeAttr('disabled');
                           if(response.code == 200){
                              iziToast.success({
                                 title: 'Success!',
                                 message: response.message,
                                 position: 'topRight'
                              });
                              $(".modal").modal('hide');
                              resetAllSelect();
                              this_form.reset();
                              tb.ajax.reload(null, false);
                           }
                           else if(response.code == 201){
                              iziToast.success({
                                 title: 'Success!',
                                 message: response.message,
                                 position: 'topRight'
                              });
                              $(".modal").modal('hide');
                              window.location.href = response.data;
                           }
                           else if(response.code == 203){
                              iziToast.success({
                                 title: 'Success!',
                                 message: response.message,
                                 position: 'topRight'
                              });
                              $(".modal").modal('hide');
                              tb.ajax.reload(null, false);
                           }

                        },error: function (jqXHR, textStatus, errorThrown) {
                           setTimeout(function () {  $('#modal_loading').modal('hide'); }, 500);
                           $("#button_submit").removeAttr('status');
                           $("#button_submit").removeAttr('disabled');
                           if(jqXHR.status == 400){
                              $('.invalid-feedback').remove();
                              $('input, textarea, select').removeClass('is-invalid');
                              Object.keys(jqXHR.responseJSON.errors).forEach(function (key) {
                                 var responseError = jqXHR.responseJSON.errors[key];
                                 var elem_name = $(this_form).find('[name=' + responseError['field'] + ']');
                                 let errorMessage = `<span class="d-flex text-danger invalid-feedback">${responseError['message']}</span>`;
                                 if (elem_name.hasClass('input-group')) {
                                    elem_name.parent().append(errorMessage);
                                 } else {
                                    elem_name.after(errorMessage);
                                 }
                                 elem_name.addClass('is-invalid');
                              });
                              Swal.fire('Oops!',jqXHR.responseJSON.message,'warning');
                           }
                           else if(jqXHR.status == 500){
                              Swal.fire('Error!',jqXHR.responseJSON.message,'error');
                           }else{
                              Swal.fire('Oops!','Something wrong try again later (' + errorThrown + ')','error');
                           }
                        },
                  });
               }
            })
      });
   
      function editAction(url, modal_text){
         save_method = 'edit';
         $("#modal").modal('show');
         $(".modal-title").text(modal_text);
         $("#form_submit")[0].reset();
         $('.invalid-feedback').remove();
         $('input.is-invalid').removeClass('is-invalid');
         $("#modal_loading").modal('show');
         $.ajax({
            url : url,
            type: "GET",
            dataType: "JSON",
            success: function(response){
               setTimeout(function () {  $('#modal_loading').modal('hide'); }, 500);
               Object.keys(response.data).forEach(function (key) {
                  var elem_name = $('[name=' + key + ']');
                  elem_name.removeClass('is-invalid');
                  if (elem_name.hasClass('selectric')) {
                     elem_name.val(response.data[key]).change().selectric('refresh');
                  }else if(elem_name.hasClass('select2')){
                     elem_name.select2("trigger", "select", { data: { id: response.data[key] } });
                  }else if(elem_name.hasClass('selectgroup-input')){
                     $("input[name="+key+"][value=" + response.data[key] + "]").prop('checked', true);
                  }else if(elem_name.hasClass('my-ckeditor')){
                     CKEDITOR.instances[key].setData(response.data[key]);
                  }else if(elem_name.hasClass('custom-control-input')){
                     $("input[name="+key+"][value=" + response.data[key] + "]").prop('checked', true);
                  }else if(elem_name.hasClass('time-format')){
                     elem_name.val(response.data[key].substr(0, 5));
                  }else if(elem_name.hasClass('format-rp')){
                     var nominal = response.data[key].toString();
                     elem_name.val(nominal.replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1."));
                  }else{
                     elem_name.val(response.data[key]);
                  }
               });
            },error: function (jqXHR, textStatus, errorThrown){

               setTimeout(function () {  $('#modal_loading').modal('hide'); }, 500);
               
               if(jqXHR.status == 400){
                  $('.invalid-feedback').remove();
                  $('input, textarea, select').removeClass('is-invalid');
                  Object.keys(jqXHR.responseJSON.errors).forEach(function (key) {
                     var responseError = jqXHR.responseJSON.errors[key];
                     var elem_name = $('[name=' + responseError['field'] + ']');
                     elem_name.after(`<span class="d-flex text-danger invalid-feedback">${responseError['message']}</span>`)
                     elem_name.addClass('is-invalid');
                  });
                  Swal.fire('Oops!',jqXHR.responseJSON.message,'warning');
               }
               else if(jqXHR.status == 404){
                  Swal.fire('Error!',jqXHR.responseJSON.message,'error');
               }else if(jqXHR.status == 500){
                  Swal.fire('Error!',jqXHR.responseJSON.message,'error');
               }else{
                  Swal.fire('Oops!','Something wrong try again later (' + errorThrown + ')','error');
               }

               setTimeout(function () {  $('#modal_loading').modal('hide'); }, 500);
               Swal.fire('Oops!','Terjadi kesalahan segera hubungi tim IT (' + errorThrown + ')','error');
            }
         });
      }
   
      function deleteAction(url, nama){
      Swal.fire({
               title: 'Anda yakin ingin menghapus ' + nama + '?',
               text: 'Setelah Anda menghapus data ini, Anda tidak akan dapat mengembalikannya',
               icon: 'warning',
               showCancelButton: true,
               confirmButtonColor: '#3085d6',
               cancelButtonColor: '#d33',
               confirmButtonText: 'Ya, Hapus Data!'
            }).then((result) => {
               if (result.value) {
                  $("#modal_loading").modal('show');
                  $.ajax({
                     url : url,
                     type: "DELETE",
                     dataType: "JSON",
                     success: function(response){
                        setTimeout(function () {  $('#modal_loading').modal('hide'); }, 500);
   
                        if(response.code === 200){
                           Swal.fire('Berhasil!',response.message,'success');
                           $("#modal").modal('hide');
                           tb.ajax.reload(null, false);
                        }else{
                           Swal.fire('Oops!',response.message,'error');
                        }
   
                     },error: function (jqXHR, textStatus, errorThrown){
                        setTimeout(function () {  $('#modal_loading').modal('hide'); }, 500);
                        Swal.fire('Oops!','Terjadi kesalahan segera hubungi tim IT (' + errorThrown + ')','error');
                     }
                  });
               }
         });
      }
   
      function reload(){
         tb.ajax.reload(null,false);
      }


      let tb = $('#tb').DataTable({
         processing: true,
         ajax: {
            url: "{{ route('master.jenis_mobil') . '/datatables' }}",
            type: 'GET'
         },
         columnDefs: [
            { className: 'text-center', targets: [0,1,3,4] },
            { className: 'col-bold', targets: [0,1,2,4] },
            { className: 'text-wrap mw-580', targets: [2] },
         ],
         columns: [
            { data: 'DT_RowIndex',searchable: false, orderable: false},
            { data: 'name' },
            { data: 'description' },
            { data: 'updated_at', render: function(data) {
                  return indonesiaDateFormat(data);
            } },
            { data: null, searchable: false, orderable: false, },
         ],
         rowCallback : function(row, data){
            let url_edit   = "{{ route('master.jenis_mobil') . '/detail/' }}" + data.id;
            let url_delete = "{{ route('master.jenis_mobil') . '/delete/' }}" + data.id;
            $('td:eq(4)', row).html(`
                  <button class="btn btn-info btn-sm me-1" onclick="editAction('${url_edit}', 'Edit Jenis Mobil')"><i class="fa fa-edit"></i></button>
                  <button class="btn btn-danger btn-sm" onclick="deleteAction('${url_delete}','${data.name}')"><i class="fa fa-trash"> </i></button>
            `);
         }
      });

   @section('modal')
   <div class="modal fade" id="modal_qris" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog mw-650px">
         <div class="modal-content">
               <div class="modal-header">
                  <h4 class="modal-title">Qris</h4>
               </div>
               <div class="modal-body py-10 px-lg-17">
                  <div class="me-n7 pe-7">
                     <img src="{{ asset('assets/images/products/s4.jpg') }}" class="w-100" alt="" srcset="">
                  </div>
               </div>
               <div class="modal-footer flex-center">
                  <button type="button" onclick="doTransaksi()" class="btn btn-success me-3">Sudah Bayar!</button>
                  <button type="button" onclick="hideQrisModal()" class="btn btn-danger me-3">Nanti Dulu</button>
               </div>
         </div>
      </div>
   </div>
   @endsection


   @if(!empty(array_intersect(['laporan/transaksi', 'laporan/keuangan', 'laporan/pendapatan-karyawan'], Auth::user()->hak_akses)))

   
   </script>

   #6E214A!important




<script>
      
   .btn-success{
      box-shadow: 0 2px 6px #a8f5b4;
      background-color: #63ed7a;
      border-color: #63ed7a;
      color: #fff;
   }

   .btn-info{
      box-shadow: 0 2px 6px #82d3f8;
      background-color: #3abaf4;
      border-color: #3abaf4;
      color: #fff;
   }

   thead.bg-primary{
      background: #4154F1!important
   }

   thead.text-light tr th h6{
      color: white!important;
   }

   .btn-warning{
      box-shadow: 0 2px 6px #ffc473;
      background-color: #ffa426;
      border-color: #ffa426;
      color: #fff;
   }

   .btn-danger{
      box-shadow: 0 2px 6px #fd9b96;
      background-color: #fc544b;
      border-color: #fc544b;
      color: #fff;
   }

   /* BUTTON HOVER */

   .btn-success:hover{
      background-color: #4cea67 !important;
      color: #fff !important;
   }

   .btn-info:hover{
      background-color: #0da8ee !important;
      color: #fff!important;
   }

   .btn-warning:hover{
      background-color: #ff990d !important;
      color: #fff !important;
   }

   .btn-danger:hover{
      background-color: #fb160a !important;
      color: #fff!important;
   }

   .bg-royal{
      background: #6E214A!important;
   }

   .btn-sm{
      display: inline-block;
      border: 1px solid transparent;
      border-radius: 0.25rem;
      font-weight: 600;
      font-size: 13px;
      line-height: 24px;
      padding: 0.3rem 0.7rem;
      letter-spacing: .5px;
      /* margin: 0 8px 10px 0; */
      text-align: center;
      white-space: nowrap;
      vertical-align: middle;
   }

   div.dataTables_processing>div:last-child>div{
      background: #6E214A!important;
   }

   .modal-header{
      padding: 1.75rem 1.75rem;
      border-bottom: 1px solid #eff2f5;
      border-top-left-radius: 0.475rem;
      border-top-right-radius: 0.475rem;
   }

   .modal-content{
      border-radius: .475rem!important;
   }

   .modal-title{
      font-weight: 600!important;
      color: #181c32;
      line-height: 1.5;
      font-family: Poppins,Helvetica,sans-serif!important;
      font-size: 1.2rem!important;
   }
</script>