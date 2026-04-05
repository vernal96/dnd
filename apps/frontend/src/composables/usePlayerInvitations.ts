import {computed, ref} from 'vue';
import {acceptPlayerInvitation, declinePlayerInvitation, fetchPlayerInvitations} from '@/services/gameApi';
import type {GameInvitationSummary} from '@/types/game';

const invitations = ref<GameInvitationSummary[]>([]);
const hasLoadedInvitations = ref(false);
const invitationsPending = ref(false);
const invitationPendingToken = ref('');
const invitationPendingAction = ref<'accept' | 'decline' | ''>('');
const invitationError = ref('');

/**
 * Управляет общим состоянием приглашений игрока на клиенте.
 */
export function usePlayerInvitations() {
    const pendingInvitationsCount = computed<number>(
        () => invitations.value.filter((invitation) => invitation.status === 'pending').length,
    );

    /**
     * Загружает приглашения пользователя из API.
     */
    async function loadInvitations(force = false): Promise<void> {
        if (invitationsPending.value || (hasLoadedInvitations.value && !force)) {
            return;
        }

        invitationsPending.value = true;
        invitationError.value = '';

        try {
            invitations.value = await fetchPlayerInvitations();
            hasLoadedInvitations.value = true;
        } catch (error) {
            invitationError.value = (error as Error).message;
        } finally {
            invitationsPending.value = false;
        }
    }

    /**
     * Принимает приглашение и обновляет локальное состояние списка.
     */
    async function acceptInvitation(token: string, characterId: number): Promise<GameInvitationSummary> {
        invitationPendingToken.value = token;
        invitationPendingAction.value = 'accept';
        invitationError.value = '';

        try {
            const invitation = await acceptPlayerInvitation(token, characterId);
            invitations.value = invitations.value.map((item) =>
                item.token === token
                    ? {
                        ...item,
                        responded_at: invitation.responded_at,
                        status: invitation.status,
                    }
                    : item,
            );

            return invitation;
        } catch (error) {
            invitationError.value = (error as Error).message;
            throw error;
        } finally {
            invitationPendingToken.value = '';
            invitationPendingAction.value = '';
        }
    }

    /**
     * Отклоняет приглашение и обновляет локальное состояние списка.
     */
    async function declineInvitation(token: string): Promise<GameInvitationSummary> {
        invitationPendingToken.value = token;
        invitationPendingAction.value = 'decline';
        invitationError.value = '';

        try {
            const invitation = await declinePlayerInvitation(token);
            invitations.value = invitations.value.map((item) =>
                item.token === token
                    ? {
                        ...item,
                        responded_at: invitation.responded_at,
                        status: invitation.status,
                    }
                    : item,
            );

            return invitation;
        } catch (error) {
            invitationError.value = (error as Error).message;
            throw error;
        } finally {
            invitationPendingToken.value = '';
            invitationPendingAction.value = '';
        }
    }

    /**
     * Сбрасывает локальное состояние приглашений при выходе пользователя.
     */
    function resetInvitations(): void {
        invitations.value = [];
        hasLoadedInvitations.value = false;
        invitationsPending.value = false;
        invitationPendingToken.value = '';
        invitationPendingAction.value = '';
        invitationError.value = '';
    }

    return {
        acceptInvitation,
        declineInvitation,
        hasLoadedInvitations,
        invitationError,
        invitationPendingAction,
        invitationPendingToken,
        invitations,
        invitationsPending,
        loadInvitations,
        pendingInvitationsCount,
        resetInvitations,
    };
}
