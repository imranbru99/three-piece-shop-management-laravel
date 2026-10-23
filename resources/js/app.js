import './bootstrap';
const menu = document.querySelector('[data-menu]');
menu?.addEventListener('click', () => {
    const open = menu.getAttribute('aria-expanded') !== 'true';
    menu.setAttribute('aria-expanded', String(open));
    document.getElementById('navigation')?.classList.toggle('open', open);
});
const grid = document.querySelector('[data-feature-grid]');
if (grid) {
    let category = 'all';
    const search = document.querySelector('[data-feature-search]');
    const filter = () => {
        let visible = 0;
        grid.querySelectorAll('[data-group]').forEach(card => {
            card.hidden = (category !== 'all' && card.dataset.group !== category) || !card.dataset.search.includes(search.value.toLowerCase().trim());
            if (!card.hidden) visible++;
        });
        document.querySelector('[data-empty]').hidden = visible > 0;
    };
    document.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', () => {
        category = button.dataset.filter;
        document.querySelectorAll('[data-filter]').forEach(b => { b.classList.toggle('active', b === button); b.setAttribute('aria-pressed',String(b === button)); });
        filter();
    }));
    search.addEventListener('input', filter);
}
const stack = document.querySelector('[data-stack]');
if (stack) {
    const modules = JSON.parse(document.getElementById('module-data').textContent);
    const presets = JSON.parse(document.getElementById('preset-data').textContent);
    const locale = document.documentElement.lang;
    const inputs = [...document.querySelectorAll('[data-module]')];
    const essentials = Object.keys(modules).filter(key => modules[key].locked);
    let selected = new Set(essentials);
    try { const stored = JSON.parse(localStorage.getItem('threepiece-stack') || '[]'); stored.filter(key => modules[key]).forEach(key => selected.add(key)); } catch {}
    const url = new URL(location.href);
    if (url.searchParams.has('stack')) selected = new Set([...essentials,...url.searchParams.get('stack').split(',').filter(key=>modules[key])]);
    if (presets[stack.dataset.initial]) selected = new Set([...essentials,...presets[stack.dataset.initial].modules]);
    if (modules[stack.dataset.selected]) selected.add(stack.dataset.selected);
    function expand(key) { modules[key].depends_on.forEach(dep=>{ if(!selected.has(dep)){selected.add(dep);expand(dep);} }); }
    const render = () => {
        [...selected].forEach(expand);
        inputs.forEach(input=>input.checked=selected.has(input.value));
        document.querySelector('[data-count]').textContent=String(selected.size);
        const tags=document.querySelector('[data-selection]'); tags.replaceChildren();
        [...selected].forEach(key=>{const tag=document.createElement('span'); tag.textContent=modules[key].name[locale];tags.append(tag);});
        document.querySelector('[data-tier]').textContent=selected.size<=8?'Essential':selected.size<=16?'Growth':'Complete';
        try{localStorage.setItem('threepiece-stack',JSON.stringify([...selected]));}catch{}
    };
    inputs.forEach(input=>input.addEventListener('change',()=>{
        if(input.checked) selected.add(input.value);
        else {
            const dependents=[...selected].filter(key=>modules[key].depends_on.includes(input.value));
            if(dependents.length){input.checked=true; document.querySelector('[data-stack-notice]').textContent=(locale==='bn'?'এই মডিউলটি প্রয়োজন: ':'Required by: ')+dependents.map(key=>modules[key].name[locale]).join(', ');return;}
            selected.delete(input.value);
        }
        document.querySelector('[data-stack-notice]').textContent='';render();
    }));
    const preset=document.querySelector('[data-preset]'); preset.value=stack.dataset.initial;
    preset.addEventListener('change',()=>{selected=new Set([...essentials,...(presets[preset.value]?.modules||[])]);render();});
    document.querySelector('[data-copy-stack]').addEventListener('click',async()=>{
        const link=new URL(location.href);link.search='';link.searchParams.set('stack',[...selected].join(','));link.searchParams.set('lang',locale);
        try{await navigator.clipboard.writeText(link.href);document.querySelector('[data-stack-notice]').textContent=locale==='bn'?'লিংক কপি হয়েছে।':'Stack link copied.';}catch{document.querySelector('[data-stack-notice]').textContent=link.href;}
    });render();
}
const pos = document.querySelector('[data-pos]');
if(pos){const total=()=>{let sum=0;pos.querySelectorAll('[data-price]').forEach(input=>{sum+=Math.max(0,Number(input.value)||0)*Number(input.dataset.price);});document.querySelector('[data-pos-total]').textContent=new Intl.NumberFormat('en-BD',{style:'currency',currency:'BDT'}).format(sum/100);};pos.addEventListener('input',total);total();}
document.querySelectorAll('[data-print]').forEach(button=>button.addEventListener('click',()=>window.print()));
document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!window.confirm(form.dataset.confirm))event.preventDefault();}));
