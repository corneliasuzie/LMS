const api = (path, opts={})=>fetch(path, Object.assign({credentials:'same-origin'},opts)).then(r=>r.json());

function el(id){return document.getElementById(id)}

let currentUser = null;

function renderUserArea(user){
  const ua = el('userArea');
  ua.innerHTML = '';
  if (!user){
    const tpl = document.getElementById('loginTpl').content.cloneNode(true);
    ua.appendChild(tpl);
    const form = ua.querySelector('#loginForm');
    form?.addEventListener('submit', loginForm);
    return;
  }
  const tpl = document.getElementById('userTpl').content.cloneNode(true);
  ua.appendChild(tpl);
  el('userName').textContent = user.name || user.nom || user.email;
  el('logoutBtn').addEventListener('click', async function(e){ e.preventDefault(); await logout(); });
}

async function getMe(){
  try{
    const res = await api('api/auth.php?action=me');
    if (res.logged){ currentUser = res.user; localStorage.setItem('user', JSON.stringify(currentUser)); return currentUser; }
  }catch(e){}
  localStorage.removeItem('user'); currentUser = null; return null;
}

async function showCourses(){
  const res = await api('api/courses.php');
  const list = el('coursesList'); list.innerHTML='';
  if (res.success){
    res.courses.forEach(c=>{
      const d=document.createElement('div');d.className='list-item';
      d.innerHTML=`<strong>${c.title}</strong><div class="small muted">${c.description||''}</div><div style="margin-top:8px"><a href="#" class="btn" onclick="enroll(${c.id})">S'inscrire</a></div>`;
      list.appendChild(d);
    })
  } else {
    list.innerHTML = '<div class="muted">Impossible de charger les cours</div>';
  }
}

async function enroll(courseId){
  const me = currentUser || await getMe();
  if (!me){ alert('Connecte-toi d\'abord'); return; }
  const res = await api('api/enroll.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({user_id:me.id,course_id:courseId})});
  if (res.success) { alert('Inscription réussie'); showEnrollments(); }
  else alert(res.message||res.error||'Erreur');
}

async function showEnrollments(){
  const me = currentUser || await getMe();
  const out = el('enrollments');
  if (!me) { out.innerHTML='<div class="muted">Connecte-toi pour voir tes inscriptions</div>'; return; }
  const res = await api('api/enroll.php?user_id='+me.id);
  out.innerHTML='';
  if (res.success){
    res.enrollments.forEach(e=>{
      const d=document.createElement('div'); d.className='list-item'; d.innerHTML=`<strong>${e.title}</strong><div class="small">Progress: ${e.progress_pct||0}%</div>`; out.appendChild(d);
    })
  } else {
    out.innerHTML = '<div class="muted">Impossible de charger vos inscriptions</div>';
  }
}

async function loginForm(e){
  e.preventDefault();
  const email = el('email').value, password = el('password').value;
  const res = await api('api/auth.php?action=login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email,password})});
  if (res.success){
    currentUser = res.user;
    localStorage.setItem('user', JSON.stringify(currentUser));
    renderUserArea(currentUser);
    await showCourses();
    await showEnrollments();
  } else alert(res.message||'Erreur');
}

async function logout(){
  await api('api/auth.php?action=logout');
  localStorage.removeItem('user'); currentUser = null;
  renderUserArea(null);
  await showCourses();
  await showEnrollments();
}

async function init(){
  const stored = localStorage.getItem('user');
  if (stored) currentUser = JSON.parse(stored);
  const me = await getMe();
  if (me) currentUser = me;
  renderUserArea(currentUser);
  document.getElementById('btnRefresh')?.addEventListener('click', ()=>{ showCourses(); showEnrollments(); });
  await showCourses();
  await showEnrollments();
}

window.addEventListener('DOMContentLoaded', init);
