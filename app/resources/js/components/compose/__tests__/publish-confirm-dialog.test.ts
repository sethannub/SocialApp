import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

import { describe, expect, it } from 'vitest';

import type { AccountBlock } from '@/lib/compose/precheck';
import type { Account } from '@/types/compose';

import { planPublish } from '../publish-confirm-dialog';

// SocialApp change: tests for the "Publish now" confirm window (variant B).

function account(id: string, overrides: Partial<Account> = {}): Account {
    return {
        id,
        platform: 'facebook',
        handle: `@${id}`,
        display_name: `Y5Media ${id}`,
        avatar_url: null,
        status: 'active',
        max_text_length: 63206,
        x_premium: false,
        ...overrides,
    };
}

const tooLongOnX: AccountBlock = {
    accountId: 'x',
    handle: '@x',
    platform: 'x',
    reasons: ['section_too_long'],
};

describe('planPublish', () => {
    it('offers to publish to every account when all are ready', () => {
        const plan = planPublish(
            [account('fb'), account('li', { platform: 'linkedin' })],
            [],
            [],
        );

        expect(plan.readyCount).toBe(2);
        expect(plan.problemCount).toBe(0);
        expect(plan.publishLabel).toBe('Publish to 2 accounts');
        expect(plan.rows.every((row) => row.state === 'ready')).toBe(true);
    });

    it('lists problem accounts first, with a reason, and offers the ready ones', () => {
        const plan = planPublish(
            [account('fb'), account('x', { platform: 'x' })],
            [tooLongOnX],
            [],
        );

        expect(plan.rows[0]?.account.id).toBe('x');
        expect(plan.rows[0]?.state).toBe('blocked');
        expect(plan.rows[0]?.problems.length).toBeGreaterThan(0);
        expect(plan.publishLabel).toBe('Publish to 1 account');
    });

    it('treats an expired connection as a problem, not a silent skip', () => {
        const plan = planPublish(
            [
                account('fb'),
                account('li', {
                    platform: 'linkedin',
                    status: 'needs_attention',
                }),
            ],
            [],
            [],
        );

        const linkedin = plan.rows.find((row) => row.account.id === 'li');
        expect(linkedin?.state).toBe('reconnect');
        expect(linkedin?.problems[0]).toContain('LinkedIn');
        expect(plan.readyCount).toBe(1);
    });

    it('offers no publish button when no account is ready', () => {
        const plan = planPublish(
            [account('x', { platform: 'x' })],
            [tooLongOnX],
            [],
        );

        expect(plan.readyCount).toBe(0);
        expect(plan.publishLabel).toBeNull();
    });
});

describe('publish paths', () => {
    const submitBar = readFileSync(
        resolve(
            process.cwd(),
            'resources/js/components/compose/submit-bar.tsx',
        ),
        'utf8',
    );
    const dialog = readFileSync(
        resolve(
            process.cwd(),
            'resources/js/components/compose/publish-confirm-dialog.tsx',
        ),
        'utf8',
    );

    it('never publishes from the main button or the Ctrl+Enter shortcut', () => {
        const start = submitBar.indexOf('async function handleSubmit()');
        const end = submitBar.indexOf('useEffect(', start);
        const handleSubmit = submitBar.slice(start, end);

        expect(start).toBeGreaterThan(-1);
        expect(handleSubmit).not.toContain('publish(id)');
        expect(handleSubmit).not.toContain('OPTIMISTIC_PUBLISH');
    });

    it('only publishes from the confirm window', () => {
        expect(submitBar.match(/publish\(id\)/g)).toHaveLength(1);
        expect(submitBar).toContain(
            'onConfirm={(readyOnly) => void handlePublishNow(readyOnly)}',
        );
        expect(submitBar).toContain('Publish now…');
    });

    it('starts keyboard focus on Cancel and says when it goes live', () => {
        expect(dialog).toContain('initialFocus={cancelRef}');
        expect(dialog).toContain('Goes live now');
    });
});
