<div class="col-md-11 mt-5">
    <div class="shadow p-3 mb-5 bg-body rounded">
        <div class="shadow-sm p-4 mb-4 bg-white boderbottom">Import</div>
        <div style="text-align: center; margin-top: 20px;">
            <button type="button" name="import" id="aasmimport" style="padding: 8px 20px; background-color: #337ab7; color: #fff; border: none; border-radius: 4px;">Import</button>
        </div>
        <div style="margin-top: 20px;">
            <input type="checkbox" name="caching_cdn" id="caching_cdn" style="margin-right: 8px; transform: scale(0.8);">
            <label for="caching_cdn" style="font-size: 14px;">Re-enable caching and/or CDN/AFD features</label>
        </div>
    </div>
</div>
<script type="text/javascript" language="javascript">
    $(document).ready(function(){
    })

    $('#aasmimport').click(function(){
        var popup = document.createElement("div");
            popup.style.display = "block";
            popup.style.position = "fixed";
            popup.style.top = "50%";
            popup.style.left = "50%";
            popup.style.transform = "translate(-50%, -50%)";
            popup.style.width = "300px";
            popup.style.padding = "20px";
            popup.style.backgroundColor = "#f0f0f0";
            popup.style.borderRadius = "4px";
            popup.style.boxShadow = "0 2px 5px rgba(0, 0, 0, 0.3)";
            popup.style.zIndex = "9999";

            // Set the dynamic string
            var dynamicString = "<?php echo addslashes(AASM_STATUS_MSG); ?>";
            popup.textContent = dynamicString;

            // Append the popup to the document body
            document.body.appendChild(popup);
    });
</script>
