<script>
    /**
     * Creates overlay over ACF Editor Fields
     */
    window.onload = function() {
        var postboxContainer = document.getElementById("postbox-container-2");

        var overlayDiv = document.createElement("div");
        overlayDiv.classList += " saveFirstOverlay";

        var overlayContent = document.createElement("div");
        overlayContent.classList += " saveFirstOverlay__message";
        overlayContent.innerHTML = "Enter title and<br>save before editing";

        overlayDiv.appendChild(overlayContent);

        postboxContainer.appendChild(overlayDiv);
    };
</script>