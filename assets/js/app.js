const api = (path, opts={})=>fetch(path, Object.assign({credentials:'same-origin'},opts)).then(r=>r.json());

function el(id){return document.getElementById(id)}

async function showCourses(){
  const res = await api('api/courses.php');
  const list = el('coursesList'); list.innerHTML='';
  if (res.success){
    res.courses.forEach(c=>{
      const d=document.createElement('div');d.className='list-item';
      d.innerHTML=`<strong>${c.title}</strong><div class="small muted">${c.description||''}</div><div style="margin-top:8px"><a href="#" class="btn" onclick="enroll(${c.id})">S'inscrire</a></div>`;
      list.appendChild(d);
    })
  }
}

async function enroll(courseId){
  const me = await api('api/auth.php?action=me');
  if (!me.logged){ alert('Connecte-toi d\'abord'); return; }
  const res = await api('api/enroll.php', {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({user_id:me.user.id,course_id:courseId})});
  if (res.success) { alert('Inscription réussie'); showEnrollments(); }
  else alert(res.message||res.error||'Erreur');
}

async function showEnrollments(){
  const me = await api('/lms/api/auth.php?action=me');
  if (!me.logged) { el('enrollments').innerHTML='<div class="muted">Connecte-toi pour voir tes inscriptions</div>'; return; }
  const res = await api('api/enroll.php?user_id='+me.user.id);
  const out = el('enrollments'); out.innerHTML='';
  if (res.success){
    res.enrollments.forEach(e=>{
      const d=document.createElement('div'); d.className='list-item'; d.innerHTML=`<strong>${e.title}</strong><div class="small">Progress: ${e.progress_pct||0}%</div>`; out.appendChild(d);
    })
  }
}

async function loginForm(e){
  e.preventDefault();
  const email = el('email').value, password = el('password').value;
  const res = await api('api/auth.php?action=login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email,password})});
  if (res.success){ document.location.reload(); } else alert(res.message||'Erreur');
}

async function init(){
  document.querySelector('#loginForm')?.addEventListener('submit', loginForm);
  document.getElementById('btnRefresh')?.addEventListener('click', ()=>{ showCourses(); showEnrollments(); });
  await showCourses();
  await showEnrollments();
}

window.addEventListener('DOMContentLoaded', init);
