export type RealtimeEventType =
  | 'game-invitation.accepted'
  | 'game-invitation.created'
  | 'game-invitation.declined';

export type RealtimeEventPayload = {
  gameId: number;
  gmUserId: number;
  invitationId: number;
  invitedUserId: number;
  status: string;
  token: string;
};

export type RealtimeEventMessage = {
  event: RealtimeEventType;
  payload: RealtimeEventPayload;
};
