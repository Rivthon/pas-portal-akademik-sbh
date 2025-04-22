<script>
        @if (session('success'))
             toastr.success("{{ session('success') }}", "Sukses", {
            positionClass: 'toast-bottom-right'
        });
        @endif

        @if (session('error'))
            toastr.error("{{ session('error') }}");
        @endif

        @if (session('info'))
            toastr.info("{{ session('info') }}");
        @endif

        @if (session('warning'))
            toastr.warning("{{ session('warning') }}");
        @endif
    </script>
