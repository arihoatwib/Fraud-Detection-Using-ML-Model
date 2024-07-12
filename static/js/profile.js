document.addEventListener('DOMContentLoaded', function() {
  var profileCircle = document.getElementById('profileCircle');
  var dropdownMenu = document.querySelector('.dropdown-menu');

  profileCircle.addEventListener('click', function() {
    console.log('Profile circle clicked');
    var isExpanded = this.getAttribute('aria-expanded') === 'true';
    this.setAttribute('aria-expanded', !isExpanded);
    dropdownMenu.classList.toggle('show', !isExpanded);
  });

  // Close the dropdown if clicked outside
  document.addEventListener('click', function(event) {
    if (!profileCircle.contains(event.target) && !dropdownMenu.contains(event.target)) {
      console.log('Clicked outside the dropdown');
      profileCircle.setAttribute('aria-expanded', 'false');
      dropdownMenu.classList.remove('show');
    }
  });
});
