import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

import { describe, expect, it } from 'vitest';

import { formatStars } from '../sidebar-footer-card';

describe('formatStars', () => {
    it('leaves small counts unabbreviated', () => {
        expect(formatStars(0)).toBe('0');
        expect(formatStars(999)).toBe('999');
    });

    it('abbreviates thousands with one decimal', () => {
        expect(formatStars(1200)).toBe('1.2k');
        expect(formatStars(4210)).toBe('4.2k');
        expect(formatStars(10000)).toBe('10k');
    });
});

describe('sidebar footer card variants', () => {
    const source = readFileSync(
        resolve(
            process.cwd(),
            'resources/js/components/layout/sidebar-footer-card.tsx',
        ),
        'utf8',
    );

    it('chooses the variant from features.billing', () => {
        expect(source).toContain('features?.billing');
    });

    it('shows an Upgrade call to action when not subscribed', () => {
        expect(source).toContain('Upgrade');
        expect(source).toContain('billing.manageUrl');
    });

    it('hides the chip for subscribed workspaces', () => {
        expect(source).toContain('billing.subscribed');
        expect(source).not.toContain("'Manage'");
        expect(source).not.toContain('Active subscription');
    });

    // SocialApp change: the Shoutrrr "Star on GitHub" / "Sponsor" card was removed.
    it('does not link to the Shoutrrr repo or sponsor page', () => {
        expect(source).not.toContain('community.repoUrl');
        expect(source).not.toContain('community.sponsorUrl');
        expect(source).not.toContain('Star on GitHub');
    });
});
