<?php
    // Estilos das telas "Esqueci a senha" e "Redefinir senha".
    // Usa só as variáveis do style.css (tema claro/escuro continua funcionando).
?>
<style>
    /* ícone do topo com o degradê da marca */
    .recovery-card .auth-icon {
        background: linear-gradient(135deg, var(--info) 0%, var(--navy) 100%);
        color: #fff;
        box-shadow: var(--shadow-sm);
    }

    /* passo a passo */
    .recovery-steps {
        list-style: none;
        display: grid;
        gap: 0.65rem;
        margin: 0 0 1.5rem;
        padding: 1rem 1.1rem;
        background: var(--bg-muted);
        border-radius: var(--radius-md);
        counter-reset: passo;
    }

    .recovery-steps li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.88rem;
        color: var(--text-muted);
        counter-increment: passo;
    }

    .recovery-steps li::before {
        content: counter(passo);
        flex-shrink: 0;
        width: 26px;
        height: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--bg-surface);
        border: 1px solid var(--border-strong);
        color: var(--link);
        font-family: var(--font-mono);
        font-weight: 700;
        font-size: 0.78rem;
    }

    /* telas de resultado (e-mail enviado / senha alterada / link inválido) */
    .recovery-result {
        text-align: center;
    }

    .recovery-result .result-icon {
        width: 76px;
        height: 76px;
        margin: 0 auto 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 2rem;
        animation: recPop 0.45s cubic-bezier(0.2, 1.4, 0.4, 1);
    }

    .recovery-result.is-ok .result-icon {
        background: rgba(31, 138, 76, 0.14);
        color: #1F8A4C;
    }

    .recovery-result.is-erro .result-icon {
        background: var(--mec-soft);
        color: var(--mec-ink);
    }

    .recovery-result h1 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--heading);
        margin-bottom: 0.5rem;
    }

    .recovery-result > p {
        color: var(--text-muted);
        font-size: 0.95rem;
        margin-bottom: 1.25rem;
    }

    .recovery-tips {
        list-style: none;
        display: grid;
        gap: 0.5rem;
        margin: 0 0 1.4rem;
        padding: 0.9rem 1rem;
        text-align: left;
        border: 1px dashed var(--border-strong);
        border-radius: var(--radius-md);
        font-size: 0.86rem;
        color: var(--text-muted);
    }

    .recovery-tips li {
        display: flex;
        gap: 0.6rem;
    }

    .recovery-tips i {
        margin-top: 0.2rem;
        color: var(--link);
    }

    .recovery-actions {
        display: grid;
        gap: 0.6rem;
    }

    /* botão de enviar com "carregando" (o e-mail pode demorar alguns segundos) */
    .btn.is-loading {
        pointer-events: none;
        opacity: 0.85;
    }

    .btn.is-loading i {
        display: none;
    }

    .btn.is-loading::before {
        content: "";
        width: 1em;
        height: 1em;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: recGirar 0.7s linear infinite;
    }

    /* olhinho de mostrar/ocultar senha */
    .input-icon .form-control.com-olho {
        padding-right: 3rem;
    }

    .input-icon .senha-olho {
        position: absolute;
        right: 0.25rem;
        top: 50%;
        transform: translateY(-50%);
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: var(--text-muted);
    }

    .input-icon .senha-olho:hover {
        background: var(--bg-muted);
    }

    /* força da senha */
    .strength {
        margin-top: 0.6rem;
    }

    .strength-bars {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.35rem;
    }

    .strength-bars span {
        height: 6px;
        border-radius: var(--radius-full);
        background: var(--border-color);
        transition: background-color var(--transition);
    }

    .strength[data-nivel="1"] .strength-bars span:nth-child(-n+1) { background: var(--mec); }
    .strength[data-nivel="2"] .strength-bars span:nth-child(-n+2) { background: var(--eletro); }
    .strength[data-nivel="3"] .strength-bars span:nth-child(-n+3) { background: var(--info); }
    .strength[data-nivel="4"] .strength-bars span:nth-child(-n+4) { background: #1F8A4C; }

    .strength-label {
        display: flex;
        justify-content: space-between;
        margin-top: 0.35rem;
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .strength-label strong {
        color: var(--heading);
    }

    .field-hint {
        margin-top: 0.4rem;
        font-size: 0.82rem;
        color: var(--text-dim);
    }

    .match-hint {
        display: none;
        align-items: center;
        gap: 0.4rem;
        margin-top: 0.45rem;
        font-size: 0.84rem;
        font-weight: 600;
    }

    .match-hint.show { display: flex; }
    .match-hint.ok { color: #1F8A4C; }
    .match-hint.erro { color: var(--mec-ink); }

    @keyframes recPop {
        from { transform: scale(0.4); opacity: 0; }
        to { transform: none; opacity: 1; }
    }

    @keyframes recGirar {
        to { transform: rotate(360deg); }
    }
</style>
