import { useRef, useState } from 'react';

import { PlatformGlyph } from '@/components/common/platform-glyph';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { PlatformPreview } from '@/lib/compose/platform-preview';
import { type AccountBlock, describeReason } from '@/lib/compose/precheck';
import { platformLabel } from '@/lib/platforms';
import { cn } from '@/lib/utils';
import type { Account, PlatformLimits, PlatformName } from '@/types/compose';

import { PlatformPreviewPanel } from './platform-preview-panel';

/**
 * SocialApp change: the "Publish now" confirm window (DESIGN_DECISIONS.md 2026-10-16,
 * variant B). Lists every selected account, ready or not, with the reason for each problem,
 * and lets the user decide whether to publish to the ready ones. Nothing is sent until the
 * publish button inside this window is pressed.
 */

export type PublishTargetRow = {
    account: Account;
    state: 'ready' | 'blocked' | 'reconnect';
    problems: string[];
};

export type PublishPlan = {
    rows: PublishTargetRow[];
    readyCount: number;
    problemCount: number;
    /** Text for the publish button, or null when nothing can be published. */
    publishLabel: string | null;
};

const EMPTY_LIMITS = (platform: PlatformName): PlatformLimits => ({
    platform,
    maxLength: 0,
    maxBytes: null,
    maxMedia: 0,
    requiresMedia: false,
    maxMediaBytes: 0,
    allowedMime: [],
    threadMax: null,
    maxImageDimensions: { width: 0, height: 0 },
    allowedVideoMime: [],
    maxVideoBytes: 0,
    maxVideoDurationSeconds: 0,
    videoAspectRatioRange: null,
});

export function planPublish(
    accounts: Account[],
    blocked: AccountBlock[],
    limits: PlatformLimits[],
): PublishPlan {
    const rows: PublishTargetRow[] = accounts.map((account) => {
        const problems: string[] = [];
        const block = blocked.find((item) => item.accountId === account.id);
        if (block) {
            const platformLimits =
                limits.find((item) => item.platform === account.platform) ??
                EMPTY_LIMITS(account.platform);
            for (const reason of block.reasons) {
                problems.push(
                    describeReason(reason, account.platform, platformLimits),
                );
            }
        }
        if (account.status === 'needs_attention') {
            problems.unshift(
                `The connection to ${platformLabel(account.platform)} has expired or was revoked. Reconnect it under Accounts.`,
            );
        }

        return {
            account,
            state:
                account.status === 'needs_attention'
                    ? 'reconnect'
                    : problems.length > 0
                      ? 'blocked'
                      : 'ready',
            problems,
        };
    });

    // Problem accounts first, so they are the first thing read.
    rows.sort(
        (a, b) => Number(a.state === 'ready') - Number(b.state === 'ready'),
    );

    const readyCount = rows.filter((row) => row.state === 'ready').length;
    const problemCount = rows.length - readyCount;
    const noun = readyCount === 1 ? 'account' : 'accounts';

    return {
        rows,
        readyCount,
        problemCount,
        publishLabel:
            readyCount === 0 ? null : `Publish to ${readyCount} ${noun}`,
    };
}

const STATE_CHIP: Record<
    PublishTargetRow['state'],
    { label: string; className: string }
> = {
    ready: {
        label: 'Ready',
        className: 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
    },
    blocked: {
        label: "Can't post",
        className: 'bg-destructive/10 text-destructive',
    },
    reconnect: {
        label: 'Needs reconnect',
        className: 'bg-amber-500/15 text-amber-700 dark:text-amber-400',
    },
};

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    accounts: Account[];
    blocked: AccountBlock[];
    limits: PlatformLimits[];
    buildPreview: (account: Account) => PlatformPreview;
    /** Workspace scheduling timezone, used for the "goes live" time. */
    tz: string;
    processing: boolean;
    /** readyOnly is true when some accounts will be skipped. */
    onConfirm: (readyOnly: boolean) => void;
    onScheduleInstead: () => void;
};

export function PublishConfirmDialog({
    open,
    onOpenChange,
    accounts,
    blocked,
    limits,
    buildPreview,
    tz,
    processing,
    onConfirm,
    onScheduleInstead,
}: Props) {
    const cancelRef = useRef<HTMLButtonElement>(null);
    const plan = planPublish(accounts, blocked, limits);
    const [selectedId, setSelectedId] = useState<string | null>(null);
    const selected =
        plan.rows.find((row) => row.account.id === selectedId) ??
        plan.rows[0] ??
        null;

    const readyPlatforms = plan.rows
        .filter((row) => row.state === 'ready')
        .map((row) => platformLabel(row.account.platform));
    const goesLiveAt = new Intl.DateTimeFormat('en-GB', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: tz,
    }).format(new Date());

    const title =
        plan.readyCount === 0
            ? 'None of these accounts can take this post yet'
            : plan.problemCount === 0
              ? `Publish now to ${plan.readyCount} ${plan.readyCount === 1 ? 'account' : 'accounts'}?`
              : `Publish now to ${plan.readyCount} of ${plan.rows.length} accounts?`;
    const description =
        plan.readyCount === 0
            ? 'Fix the problems below, or schedule the post for later.'
            : plan.problemCount === 0
              ? 'Each account gets its own version. Posts appear publicly within seconds.'
              : `${plan.problemCount} ${plan.problemCount === 1 ? "account can't" : "accounts can't"} take this post. If you go ahead, ${plan.problemCount === 1 ? 'it is' : 'they are'} skipped and marked Failed with the reason, and you can retry after fixing it.`;

    return (
        <Dialog open={open} onOpenChange={(next) => onOpenChange(next)}>
            <DialogContent
                initialFocus={cancelRef}
                className="max-h-[90dvh] overflow-y-auto sm:max-w-3xl"
            >
                <DialogHeader>
                    <DialogTitle className="text-[15px] font-semibold text-balance">
                        {title}
                    </DialogTitle>
                    <DialogDescription className="text-[12.5px] text-muted-foreground">
                        {description}
                    </DialogDescription>
                </DialogHeader>

                <div className="grid gap-4 md:grid-cols-2">
                    <ul
                        aria-label="Accounts"
                        className="flex min-w-0 flex-col overflow-hidden rounded-lg border border-border"
                    >
                        {plan.rows.map((row) => {
                            const chip = STATE_CHIP[row.state];
                            const isSelected =
                                selected?.account.id === row.account.id;

                            return (
                                <li
                                    key={row.account.id}
                                    className="border-b border-border last:border-b-0"
                                >
                                    <button
                                        type="button"
                                        aria-pressed={isSelected}
                                        onClick={() =>
                                            setSelectedId(row.account.id)
                                        }
                                        className={cn(
                                            'flex w-full items-start gap-2.5 px-3 py-2.5 text-left transition-colors hover:bg-muted/60 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-ring',
                                            isSelected && 'bg-muted',
                                        )}
                                    >
                                        <span className="mt-0.5 grid size-[18px] shrink-0 place-items-center rounded-[5px] bg-muted text-foreground">
                                            <PlatformGlyph
                                                platform={row.account.platform}
                                            />
                                        </span>
                                        <span className="min-w-0 flex-1">
                                            <span className="block truncate text-[12.5px] font-medium">
                                                {row.account.display_name ??
                                                    row.account.handle}
                                            </span>
                                            <span className="block text-[11.5px] text-muted-foreground">
                                                {platformLabel(
                                                    row.account.platform,
                                                )}{' '}
                                                · {row.account.handle}
                                            </span>
                                            {row.problems.map((problem) => (
                                                <span
                                                    key={problem}
                                                    className="mt-1 block text-[11.5px] text-foreground"
                                                >
                                                    {problem}
                                                </span>
                                            ))}
                                        </span>
                                        <span
                                            className={cn(
                                                'shrink-0 rounded-[4px] px-1.5 py-0.5 text-[10.5px] font-semibold',
                                                chip.className,
                                            )}
                                        >
                                            {chip.label}
                                        </span>
                                    </button>
                                </li>
                            );
                        })}
                    </ul>

                    <div className="min-w-0">
                        {selected && (
                            <>
                                <div className="mb-1.5 text-[10.5px] font-medium tracking-wider text-muted-foreground uppercase">
                                    Preview ·{' '}
                                    {platformLabel(selected.account.platform)}
                                </div>
                                <PlatformPreviewPanel
                                    preview={buildPreview(selected.account)}
                                />
                            </>
                        )}
                    </div>
                </div>

                {plan.readyCount > 0 && (
                    <p className="rounded-md bg-amber-500/10 px-3 py-2 text-[12.5px] text-amber-800 dark:text-amber-300">
                        <span className="font-semibold">Goes live now</span> on{' '}
                        {readyPlatforms.join(', ')} · {goesLiveAt} ({tz}). It
                        can be deleted afterwards, but people may already have
                        seen it.
                    </p>
                )}

                <DialogFooter className="items-center gap-2 sm:justify-between">
                    <button
                        type="button"
                        onClick={onScheduleInstead}
                        className="text-[12px] text-muted-foreground underline underline-offset-2 hover:text-foreground"
                    >
                        Schedule instead
                    </button>
                    <div className="flex gap-2">
                        <Button
                            ref={cancelRef}
                            variant="outline"
                            onClick={() => onOpenChange(false)}
                        >
                            Cancel
                        </Button>
                        {plan.publishLabel !== null && (
                            <Button
                                disabled={processing}
                                onClick={() => onConfirm(plan.problemCount > 0)}
                            >
                                {plan.publishLabel}
                            </Button>
                        )}
                    </div>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
