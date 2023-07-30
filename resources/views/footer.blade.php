</body>
<script type="text/javascript">
    var $fileInput = $('.file-input');
    var $droparea = $('.file-drop-area');

    // highlight drag area
    $fileInput.on('dragenter focus click', function() {
        $droparea.addClass('is-active');
    });

    // back to normal state
    $fileInput.on('dragleave blur drop', function() {
        $droparea.removeClass('is-active');
    });

    // change inner text
    $fileInput.on('change', function() {
        var filesCount = $(this)[0].files.length;
        var $textContainer = $(this).prev();

        if (filesCount === 1) {
            // if single file is selected, show file name
            var fileName = $(this).val().split('\\').pop();
            $textContainer.text(fileName);
        } else {
            // otherwise show number of files
            $textContainer.text(filesCount + ' files selected');
        }
    });

    $(document).ready(function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#multi-file-upload-ajax').submit(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            let TotalFiles = $('#multiplefileupload')[0].files.length; //Total files
            let files = $('#multiplefileupload')[0];
            for (let i = 0; i < TotalFiles; i++) {
                formData.append('files' + i, files.files[i]);
            }
            formData.append('TotalFiles', TotalFiles);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: "{{ url('check-file') }}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: (data) => {
                    $('#validation').show();
                    let checkerlogs = $('#tbl_validation').DataTable({
                        destroy: true,
                        "scrollX": true,
                        "scrollCollapse": true,
                        processing: true,
                        dom: 'Bfrtip',
                        buttons: [{
                                extend: 'csvHtml5',
                                title: 'File Checker',
                                titleAttr: 'Export CSV',
                                text: 'CSV <i class="fa fa-arrow-circle-down"></i>',
                            },
                            {
                                extend: 'excelHtml5',
                                title: '',
                                titleAttr: 'Export Excel',
                                text: 'Excel <i class="fa fa-arrow-circle-down"></i>',
                                filename: function() {
                                    return 'File Checker';
                                },
                                customizeData: function(data) {
                                    for (var i = 0; i < data.body
                                        .length; i++) {
                                        for (var j = 0; j < data.body[i]
                                            .length; j++) {
                                            data.body[i][j] = '\u200C' +
                                                data.body[i][j];
                                        }
                                    }
                                },
                            },
                            'colvis'
                        ],
                        columnDefs: [{
                                targets: "_all",
                                createdCell: function(cell, cellData, rowData,
                                    rowIndex, colIndex) {
                                    var $cell = $(cell)
                                    if (cellData != null) {
                                        var linebreakes = cellData.split(
                                            /\r\n|\r|\n|br/).length
                                    } else {
                                        var linebreakes = ''
                                    }

                                    //jquery wrap a new class around the html structure
                                    $(cell).contents().wrapAll(
                                        "<div class='content'></div>");
                                    //get the new class
                                    var $content = $cell.find(".content");
                                    //if there are more line as 12
                                    if (linebreakes > 2) {
                                        //change class and reduce height
                                        $content.css({
                                            "height": "40px",
                                            "overflow": "hidden"
                                        })
                                        //add button only for this long cells
                                        $(cell).append($(
                                            "<a href='#'>view more..</a>"
                                            ));
                                    }
                                    //get IF of this new button
                                    $btn = $(cell).find("a");
                                    //store flag
                                    $cell.data("isLess", true);
                                    //eval click on button
                                    $btn.click(function() {
                                        //create local variable and assign prev. stored flag
                                        var isLess = $cell.data(
                                            "isLess");
                                        //ternary check if this flag is set and manipulte/reverse button
                                        $content.css("height",
                                            isLess ? "auto" :
                                            "40px")
                                        $(this).text(isLess ?
                                            'view less.' :
                                            'view more..')
                                        //invert flag
                                        $cell.data("isLess", !
                                            isLess)
                                    })
                                }
                            }

                        ]
                    });

                    checkerlogs.clear().draw();

                    $.each(data['logs'], (index, value) => {
                        checkerlogs.row.add([value['error_type'], value[
                                'filename'], value['merchant_code'],
                            value['transaction_date'], value[
                                'transaction_no'], value[
                                'terminal_no'], value[
                                'error_description']
                        ]);

                    });
                    checkerlogs.draw();
                    $('#alert-nottally').show();
                    $('#alert-nottally').delay(6000).fadeOut('slow');
                    // }
                },
                beforeSend: function() {
                    $('#loading').show();
                    $('.class-form').addClass("blured");
                    $('.tbl-validation').addClass("blured");

                },
                complete: function() {
                    $('#loading').hide();
                    $('.class-form').removeClass("blured");
                    $('.tbl-validation').removeClass("blured");
                },
                error: (data) => {
                    console.log(data);

                }
            });
        });
    });
</script>

</html>
