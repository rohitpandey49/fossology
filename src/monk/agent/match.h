/*
Author: Daniele Fognini, Andreas Wuerl
Copyright (C) 2013-2014, Siemens AG

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
*/

#ifndef MONK_AGENT_MATCH_H
#define MONK_AGENT_MATCH_H

#include <glib.h>

#include "monk.h"
#include "string_operations.h"
#include "diff.h"

#define MATCH_TYPE_FULL 0
#define MATCH_TYPE_DIFF 1

typedef struct {
  License* license;
  union {
    DiffPoint* full;
    DiffResult* diff;
  } ptr;
  int type;
} Match;

typedef struct {
  int (*onAll)(MonkState* state, File* file, GArray* matches);
  int (*onNo)(MonkState* state, File* file);
  int (*onFull)(MonkState* state, File* file, License* license, DiffMatchInfo* matchInfo);
  int (*onDiff)(MonkState* state, File* file, License* license, DiffResult* diffResult);
  int (*ignore)(MonkState* state, File* file);
} MatchCallbacks;

void match_array_free(GArray* matches);
Match* match_array_get(GArray* matches, guint i);

void match_free(Match* match);

#if GLIB_CHECK_VERSION(2,32,0)
void match_destroyNotify(gpointer matchP);
#endif

size_t match_getStart(const Match* match);
size_t match_getEnd(const Match* match);

gint compareMatchByRank(gconstpointer a, gconstpointer b);
gint compareMatchIncluded(gconstpointer a, gconstpointer b);
Match* greatestMatchInGroup(GArray* matches, GCompareFunc compare);

GArray* findAllMatchesBetween(File* file, Licenses* licenses, unsigned maxAllowedDiff, unsigned minAdjacentMatches, unsigned maxLeadingDiff);

int matchPFileWithLicenses(MonkState* state, long pFileId, Licenses* licenses, MatchCallbacks* callbacks);
int matchFileWithLicenses(MonkState* state, File* file, Licenses* licenses, MatchCallbacks* callbacks);

void findDiffMatches(File* file, License* license,
                     size_t textStartPosition, size_t searchStartPosition,
                     GArray* matches,
                     unsigned maxAllowedDiff, unsigned minAdjacentMatches);

GArray* filterNonOverlappingMatches(GArray* matches);
int processMatches(MonkState* state, File* file, GArray* matches, MatchCallbacks* callbacks);

char* formatMatchArray(GArray* matchInfo);

#endif // MONK_AGENT_MATCH_H
