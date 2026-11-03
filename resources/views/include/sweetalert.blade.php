@if (Session::has('status'))
    <script type="text/javascript">
        $(document).ready(function() {
            swal({
                html: true,
                title: "Info",
                text:  "{!! Session::get('status') !!}",
                type:  "info"
            });
        });
    </script>
@endif
@foreach (Session::get('flash_notification', []) as $flash)
    <script type="text/javascript">
        $(document).ready(function() {
            level = "{{ $flash['level'] }}";
            if (level == 'danger') {
                message = {
                    html: true,
                    title: "Error",
                    text:  "{!! $flash['message'] !!}",
                    type:  "error"
                };
            } else if (level == 'info') {
                message = {
                    html: true,
                    title: "Info",
                    text:  "{!! $flash['message'] !!}",
                    type:  "info"
                };
            } else if (level == 'success') {
                message = {
                    html: true,
                    title: "Success",
                    text:  "{!! $flash['message'] !!}",
                    type:  "success"
                };
            } else if (level == 'warning') {
                message = {
                    html: true,
                    title: "Warning",
                    text:  "{!! $flash['message'] !!}",
                    type:  "warning"
                };
            }
            swal(message);
        });
    </script>
@endforeach
