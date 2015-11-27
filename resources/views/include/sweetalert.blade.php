@if (Session::has('flash_notification.message'))
    <script type="text/javascript">
        $(document).ready(function() {
            level = "{{ Session::get('flash_notification.level') }}";
            if (level == 'danger') {
                message = {
                    html: true,
                    title: "Error",
                    text:  "{!! Session::get('flash_notification.message') !!}",
                    type:  "error"
                };
            } else if (level == 'info') {
                message = {
                    html: true,
                    title: "Info",
                    text:  "{!! Session::get('flash_notification.message') !!}",
                    type:  "info"
                };
            } else if (level == 'success') {
                message = {
                    html: true,
                    title: "Success",
                    text:  "{!! Session::get('flash_notification.message') !!}",
                    type:  "success"
                };
            } else if (level == 'warning') {
                message = {
                    html: true,
                    title: "Warning",
                    text:  "{!! Session::get('flash_notification.message') !!}",
                    type:  "warning"
                };
            }
            swal(message);
        });
    </script>
@endif
