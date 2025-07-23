const connexion = document.getElementById('connexion');
const deconnexion = document.getElementById('deconnexion');
const bonjour = document.getElementById('bonjour');

if (localStorage.getItem('token')) {
  document.getElementById('dashboard').style.display = 'block';
  document.getElementById('connect').style.display = 'none';
}

connexion.addEventListener('click', () => {
  const username = document.getElementById('username').value;
  const password = document.getElementById('password').value;

  connexion.innerHTML += ' <svg id="loader" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-loader-circle-icon lucide-loader-circle"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';

  fetch('http://phpjwt/back/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ username: username, password: password })
  }).then(response => {
    return response.json();
  }).then(data => {
    if (data.token) {
      localStorage.setItem('token', data.token);
      document.getElementById('dashboard').style.display = 'block';
      document.getElementById('connect').style.display = 'none';
      document.getElementById('error').innerHTML = '';
      document.getElementById('error').style.display = 'none';
      document.getElementById('username').value = '';
      document.getElementById('password').value = '';
      
    } else {
      document.getElementById('error').style.display = 'block';
      document.getElementById('error').innerHTML = data.error;
    }
    connexion.textContent = 'Connexion';
  });
});

deconnexion.addEventListener('click', () => {
  localStorage.removeItem('token');
  document.getElementById('dashboard').style.display = 'none';
  document.getElementById('connect').style.display = 'flex';
});

bonjour.addEventListener('click', () => {
  fetch('http://phpjwt/back/', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + localStorage.getItem('token')
    },
    // body: JSON.stringify({ data: "ce qu'on veut" })
  }).then(response => {
    return response.json();
  }).then(data => {
    if (data.message) {
      document.getElementById('message').innerHTML += '<span>' + data.message + '</span>';
    } else if (data.error) {
      alert('Veuillez vous connecter');
      localStorage.removeItem('token');
      document.getElementById('dashboard').style.display = 'none';
      document.getElementById('connect').style.display = 'block';
      return;
    }
  });
});