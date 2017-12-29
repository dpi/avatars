(function ($, Drupal) {

  Drupal.behaviors.avatarsDrag = {
    attach: function attach(context, settings) {
      var tableDrag = Drupal.tableDrag.xyz;

      // Rewire the tabledrag ondrop function.
      // Changes the value of the region form element to the region the row now
      // sits beneath.
      // Inspired by blocks.js:tableDrag.onDrop
      tableDrag.onDrop = function () {
        var dragObject = this;
        var $rowElement = $(dragObject.rowObject.element);

        // Reverse traversal of siblings until first grouping row is found.
        var $regionElement = $($rowElement).prevAll('[data-tabledrag-region]').first();

        // Get the region ID.
        var regionId = $($regionElement).attr('data-tabledrag-region');

        // Assign the value of the region select element the value of the region
        // where this row was dropped under.
        var formElement = $($rowElement).find('.tabledrag-region-value');
        $(formElement).val(regionId);
      };


      // Update the row weights in a specified region.
      // Inspired by blocks.js:updateBlockWeights
      function updateWeights(table, regionId) {
        var $regionTr = $(table).find('[data-tabledrag-region=' + regionId + ']');

        // Get all the rows in this region.
        var $regionItems = $($regionTr).nextUntil('[data-tabledrag-region]');

        var weight = 0;
        $($regionItems).find('.tabledrag-region-weight').val(function () {
          console.log(this);
          return ++weight;
        });
      }

      // Move the row to a different region if the user selects the region.
      // This makes the form accessible, circumventing tabledrag.
      // Inspired by blocks.js:$(context).find('select.block-region-select')...
      $(context).find('.tabledrag-region-value').once('tabledrag-region-change').on('change', function (event) {
        var table = $(this).closest('table');
        var $currentTr = $(this).closest('tr');
        var select = $(this);

        var newRegionId = $(select).val();
        var $newRegionTr = $($currentTr).siblings('[data-tabledrag-region=' + newRegionId + ']');

        // Move this row to the end of the region.
        $($newRegionTr).nextUntil('[data-tabledrag-region]').last().after($currentTr);

        // Update weight fields according to new visual order.
        updateWeights(table, newRegionId);

        select.trigger('blur');
      });
    }
  }

})(jQuery, Drupal);
