<script>
     function loadLivewireComponent(componentName, divname, pageid) {
            fetch(`/page/component/${componentName}/${pageid}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text(); // Get the response as text
                })
                .then(html => {
                    // Inject the HTML into the container
                    document.getElementById(divname).innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading component:', error);
                });
        }

       
</script>