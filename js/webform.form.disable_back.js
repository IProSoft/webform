/**
 * @file
 * JavaScript to disable back button.
 */

(function () {
  // From: http://stackoverflow.com/questions/17962130/restrict-user-to-refresh-and-back-forward-in-any-browser
  window.history.pushState({ page: 1 }, 'Title 1', '#no-back');
  window.onhashchange = function (event) {
    window.location.hash = 'no-back';
  };
})();
