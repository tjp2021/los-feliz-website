(function(){var p=location.pathname.replace(/\/?$/,'/');[].forEach.call(document.querySelectorAll('#menu a'),function(a){var h;try{h=new URL(a.href).pathname.replace(/\/?$/,'/')}catch(e){return}if(h===p){a.setAttribute('aria-current','page');if(!a.parentNode.classList.contains('nav-donate'))a.classList.add('active');}});})();
(function(){[].forEach.call(document.querySelectorAll('[class*="lfar-anim-"]'),function(el){var m=el.className.match(/\blfar-anim-([\w-]+)/);if(m&&!el.dataset.anim)el.dataset.anim=m[1];});})();
(function(){var d=document.documentElement;d.classList.add("js");function go(){var h=document.querySelector(".hero");if(h)h.classList.add("go");}function tryGo(){if(document.visibilityState==="visible"){requestAnimationFrame(go);return true}return false}document.addEventListener("DOMContentLoaded",function(){if(!tryGo())document.addEventListener("visibilitychange",function v(){if(tryGo())document.removeEventListener("visibilitychange",v)})});setTimeout(go,6000);})();

(function(){
  var $=function(s,r){return (r||document).querySelector(s)}, $$=function(s,r){return [].slice.call((r||document).querySelectorAll(s))};

  // mobile menu
  var burger=$('#burger'), menu=$('#menu');
  if(burger&&menu){
    burger.addEventListener('click',function(){var o=menu.classList.toggle('open');burger.setAttribute('aria-expanded',o);burger.setAttribute('aria-label',o?'Close menu':'Open menu');});
    menu.addEventListener('click',function(e){if(e.target.tagName==='A'){menu.classList.remove('open');burger.setAttribute('aria-expanded','false');}});
  }
  var nav=$('#navbar'), root=document.documentElement;
  function sizes(){if(!nav)return;var n=nav.offsetHeight;root.style.setProperty('--navh',n+'px');root.style.scrollPaddingTop=(n+12)+'px';}
  sizes();window.addEventListener('resize',sizes);window.addEventListener('load',sizes);

  // The city sketch used to run on #city. The hero is a still photo now.

  // count-up numbers (final values are already in the HTML)
  if('IntersectionObserver' in window){
    var cio=new IntersectionObserver(function(es){es.forEach(function(en){
      if(!en.isIntersecting) return; var el=en.target; cio.unobserve(el);
      var end=+el.dataset.count, suf=el.dataset.suffix||'', t0=null, dur=1600;
      function step(t){ if(!t0) t0=t; var p=Math.min(1,(t-t0)/dur), e=1-Math.pow(1-p,3);
        el.textContent=Math.round(end*e)+(p===1?suf:''); if(p<1) requestAnimationFrame(step); }
      requestAnimationFrame(step);
    });},{threshold:.4});
    $$('[data-count]').forEach(function(el){ if(el.getBoundingClientRect().top>window.innerHeight) cio.observe(el); });
  }

  // Petfinder panel
  var pfBtn=$('#pf-btn'), pfPanel=$('#pf-panel');
  if(pfBtn&&pfPanel) pfBtn.addEventListener('click',function(){var o=pfPanel.classList.toggle('open');pfBtn.setAttribute('aria-expanded',o);pfBtn.firstChild.nodeValue=o?'Hide the full list ':'See all adoptable animals ';});

  // Happy Tales: each click reveals the next three #happytales posts
  var talesBtn=$('#tales-btn');
  if(talesBtn) talesBtn.addEventListener('click',function(){
    var next=$$('#stories .story[hidden]');
    next.slice(0,3).forEach(function(li,i){li.hidden=false;li.style.animationDelay=(i*.15)+'s';li.classList.remove('run','fade-up');void li.offsetWidth;li.classList.add('run','fade-up');});
    if(next.length<=3){
      var a=document.createElement('a');a.className='btn btn-ghost';a.href='#';a.textContent='See all #happytales on Instagram';
      talesBtn.replaceWith(a);
    }
  });


  // back to top
  var toTop=$('#toTop');
  if(toTop){
    function onScroll(){toTop.classList.toggle('show',window.scrollY>500);}
    window.addEventListener('scroll',onScroll,{passive:true});onScroll();
    toTop.addEventListener('click',function(e){e.preventDefault();window.scrollTo({top:0,behavior:'smooth'});});
  }

  // scroll-in animations (content stays visible if this never runs)
  if(!('IntersectionObserver' in window)) return;
  var io=new IntersectionObserver(function(entries){
    entries.forEach(function(en){ if(en.isIntersecting){var el=en.target;el.classList.add('run',el.dataset.anim);io.unobserve(el);} });
  },{rootMargin:'0px 0px 8% 0px'});
  $$('[data-anim]').forEach(function(el){
    if(el.getBoundingClientRect().top<window.innerHeight){ if(el.dataset.anim==='pl') el.classList.add('run'); return; }
    io.observe(el);
  });
})();
