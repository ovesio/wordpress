jQuery(document).ready(function ($) {
  // Initialize ajax request
  $(document).on("click", ".ovesio-translate-ajax-request", function (e) {
    e.preventDefault();
    const $link = $(this);

    $.ajax({
      url: $link.attr("href") + "&translate=true",
      beforeSend: function () {
        $(".ovesio-loader-overlay-container").show();
      },
      success: function (response) {
        $(".ovesio-loader-overlay-container").hide();
        window.location.reload();
      },
      error: function (xhr, status, error) {
        $(".ovesio-loader-overlay-container").hide();

        let errorMessage = "Request failed";
        if (xhr.responseJSON && xhr.responseJSON.data) {
          errorMessage = xhr.responseJSON.data;
        }

        // Show alert
        alert(errorMessage);
      },
    });
  });

  // AI Engine selection handler
  const selectedEngine = $("#ovesio_api_form_table #ovesio_api_engine");
  const initial = selectedEngine.val();

  $(
    `#ovesio_api_form_table .section[data-section='${initial}'] input.required`
  ).attr("required", "required"); // Add required attribute
  $(`#ovesio_api_form_table .section[data-section='${initial}']`).show();

  selectedEngine.on("change", function () {
    const selected = $(this).val();
    $("#ovesio_api_form_table .section input.required").removeAttr("required");
    $(
      `#ovesio_api_form_table .section[data-section='${selected}'] input.required`
    ).attr("required", "required");
    $("#ovesio_api_form_table .section").hide();
    $(`#ovesio_api_form_table .section[data-section='${selected}']`).show();
  });

  // Update Range Input Value
  $('#ovesio_api_form_table input[type="range"]').on("input", function () {
    const value = $(this).val();
    $(this).next(".range-value").text(value);
  });
});
