$(document).ready(function(){
    flatpickr('#dateRangeFilter',{
        locale: locale,
        mode: "range",
        dateFormat: "d-m-Y",
    })

    flatpickr('#dateRange',{
        locale: locale,
        mode: "range",
        dateFormat: "d-m-Y",
    })

    $('.select2').select2();

    $('form').on('submit', function(e){
        $(this).find('button[type="submit"]').attr('disabled', true);
    });

    $('.deleteForm button').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Estas seguro de eliminar?',
            text: "Estos cambios no se pueden revertir!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si',
            cancelButtonText: 'No'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    'Eliminado!',
                    'El elemento ha sido eliminado.',
                    'success'
                )
                $('.deleteForm').submit();
                return true;
            }else{
                return false;
            }
        })
    });

});

function changePasswordView(elementClickedId, elementId){
    let element = document.getElementById(elementId);
    let elementClicked = document.getElementById(elementClickedId);

    if(element.type == 'password'){
        element.type = 'text';
        elementClicked.innerHTML = `<i data-feather="eye-off"></i>`;
    }else{
        element.type = 'password';
        elementClicked.innerHTML = `<i data-feather="eye"></i>`;
    }

    if (feather) {
        feather.replace();
    }
}


function instanceEditor(element)
{
    let input = element.getAttribute('data-input-id');
    let evalButtonsJson = element.getAttribute('data-eval-buttons');
    let options = {
        container:
            [
                [{'header': [1, 2, 3, 4, 5, 6, false]}],
                [{'align': []}],
                [],
                ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                [{'color': []}, {'background': []}],          // dropdown with defaults from theme
                ['clean'],
                ['delete_all'],
                [{'list': 'ordered'}, {'list': 'bullet'}],
                [{'indent': '-1'}, {'indent': '+1'}],          // outdent/indent
                [],
                ['link', 'image'],
                [],


            ],
        handlers: {
            "variables": function (value) {
                if (value) {
                    const cursorPosition = this.quill.getSelection().index;
                    this.quill.insertText(cursorPosition, value);
                    this.quill.setSelection(cursorPosition + value.length);
                }
            },
            "delete_all": function () {
                this.quill.deleteText(0,this.quill.getLength());

            },
        }
    };

    if(evalButtonsJson){

        let evalButtons = JSON.parse(evalButtonsJson);

        let buttons = [];
        for (let i = 0; i < evalButtons.length; i++) {
            buttons.push(evalButtons[i]['variable']);
        }

        options.container.push([{'variables':buttons}]);

    }

    let quill = new Quill(element, {
        modules: {
            imageResize: {
                displaySize: true // default false
            },
            'toolbar': options
        },
        theme: 'snow'

    });

    quill.getModule('toolbar').addHandler('image', () => {
        selectLocalImage(quill);
    });


    let button = document.querySelectorAll('.ql-delete_all');
    for (let i = 0; i < button.length; i++) {

        button[i].innerHTML = '<i data-feather="trash"></i>';
    }



    const placeholderPickerItems = Array.prototype.slice.call(document.querySelectorAll('.ql-variables .ql-picker-item'));

    placeholderPickerItems.forEach(item => item.textContent = item.dataset.value);

    let dropdowns = document.querySelectorAll('.ql-variables .ql-picker-label');
    for (let i = 0; i < dropdowns.length; i++) {

        dropdowns[i].innerHTML = 'Insertar Variable';
    }


    if(evalButtonsJson){

        let evalButtons = JSON.parse(evalButtonsJson);
        let options = $('.ql-variables > .ql-picker-options > .ql-picker-item');

        options.each(function () {

            for (let i = 0; i < evalButtons.length; i++) {
                if($(this).text() == evalButtons[i]['variable']){
                    $(this).text(evalButtons[i]['label']);
                }

            }

        })

    }

    quill.on('text-change', function(delta, oldDelta, source) {

        document.getElementById(input).value = quill.container.firstChild.innerHTML;
    });

    document.getElementById(input).onchange = function () {
        let delta = quill.clipboard.convert(this.value);
        quill.setContents(delta, 'silent');
    }

    if (feather) {
        feather.replace({
            width: 14,
            height: 14
        });
    }
}

function customNotify(title, message, type){
    $.notify({
            title: title,
            message: message
        },
        {
            type: type,
            allow_dismiss:true,
            newest_on_top:false ,
            mouse_over:false,
            showProgressbar:false,
            spacing:10,
            timer:false,
            placement:{
                from:'top',
                align:'center'
            },
            offset:{
                x:30,
                y:30
            },
            delay:1000 ,
            z_index:10000,
            animate:{
                enter:'animated bounce',
                exit:'animated bounce'
            }
        });
}